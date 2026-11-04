from __future__ import annotations

import hashlib
import json
import os
import shutil
import subprocess
import tempfile
import time
from pathlib import Path


source = Path(os.environ["AXOLOTL_SOURCE"]).resolve()
pureperms = Path(os.environ["PUREPERMS_PHAR"]).resolve()
purechat = Path(os.environ["PURECHAT_PHAR"]).resolve()
php_binary = os.environ.get("PHP_BIN", "php")
expected_server_commit = "b16cc69974d4e805d21e6328ab77c0e17cd7116d"

if not (source / "src" / "PocketMine.php").is_file() or not (source / "vendor" / "autoload.php").is_file():
    raise RuntimeError("Pinned Axolotl-PM API-floor source and Composer dependencies are required")
server_commit = subprocess.run(
    ["git", "-C", str(source), "rev-parse", "HEAD"],
    check=True,
    capture_output=True,
    text=True,
).stdout.strip()
if server_commit != expected_server_commit:
    raise RuntimeError(f"Expected Axolotl-PM 5.0.0 source {expected_server_commit}, got {server_commit}")

hashes = {
    "PurePerms": hashlib.sha256(pureperms.read_bytes()).hexdigest(),
    "PureChat": hashlib.sha256(purechat.read_bytes()).hexdigest(),
}
expected_hashes = {
    "PurePerms": os.environ["PUREPERMS_SHA256"],
    "PureChat": os.environ["PURECHAT_SHA256"],
}
if hashes != expected_hashes:
    raise RuntimeError(f"Build output hash mismatch: expected {expected_hashes}, got {hashes}")

with tempfile.TemporaryDirectory(prefix="purechat-runtime-", dir=os.environ.get("RUNNER_TEMP")) as temporary:
    root = Path(temporary)
    data = root / "data"
    plugins = root / "plugins"
    data.mkdir()
    plugins.mkdir()
    shutil.copy2(pureperms, plugins / "PurePerms.phar")
    shutil.copy2(purechat, plugins / "PureChat.phar")
    (data / "server.properties").write_text(
        "language=eng\nserver-ip=127.0.0.1\nserver-port=0\nenable-ipv6=off\n"
        "enable-query=off\nxbox-auth=off\nlevel-type=FLAT\nview-distance=2\nmax-players=1\n",
        encoding="utf-8",
    )
    (data / "pocketmine.yml").write_text(
        "settings:\n  async-workers: 2\n  enable-dev-builds: true\n  send-usage: false\n"
        "auto-report:\n  enabled: false\nauto-updater:\n  enabled: false\n"
        "network:\n  upnp-forwarding: false\n",
        encoding="utf-8",
    )

    log = root / "server.log"
    with log.open("w", encoding="utf-8") as output:
        process = subprocess.Popen(
            [
                php_binary,
                str(source / "src" / "PocketMine.php"),
                "--no-wizard",
                "--disable-ansi",
                f"--data={data}",
                f"--plugins={plugins}",
            ],
            cwd=source,
            stdin=subprocess.PIPE,
            stdout=output,
            stderr=subprocess.STDOUT,
            text=True,
        )
        try:
            deadline = time.monotonic() + 120
            while time.monotonic() < deadline and process.poll() is None:
                content = log.read_text(encoding="utf-8", errors="replace")
                if "Done (" in content or "/CRITICAL]:" in content or "/EMERGENCY]:" in content:
                    break
                time.sleep(0.25)

            content = log.read_text(encoding="utf-8", errors="replace")
            ready = "Done (" in content
            if ready and process.stdin is not None:
                for command in ("ppinfo", "setformat Guest global &7[Guest] {display_name} > {msg}"):
                    process.stdin.write(command + "\n")
                    process.stdin.flush()
                    time.sleep(1)
                process.stdin.write("stop\n")
                process.stdin.flush()
            if process.poll() is None:
                try:
                    exit_code = process.wait(timeout=30)
                except subprocess.TimeoutExpired:
                    process.kill()
                    exit_code = process.wait()
            else:
                exit_code = process.wait()
        finally:
            if process.poll() is None:
                process.kill()
                process.wait()

    content = log.read_text(encoding="utf-8", errors="replace")
    errors = [
        line
        for line in content.splitlines()
        if any(marker in line for marker in ("/ERROR]:", "/CRITICAL]:", "/EMERGENCY]:"))
    ]
    evidence = {
        "server_commit": server_commit,
        "plugin_sha256": hashes,
        "server_ready": ready,
        "pureperms_enabled": "Enabling PurePerms v2.1.0" in content,
        "purechat_enabled": "Enabling PureChat v2.1.0" in content,
        "ppinfo": "You are currently using PurePerms v2.1.0" in content,
        "setformat": "You set the chat format of the group" in content,
        "exit_code": exit_code,
        "errors": errors,
    }
    print(json.dumps(evidence, separators=(",", ":")), flush=True)
    if (
        not ready
        or not evidence["pureperms_enabled"]
        or not evidence["purechat_enabled"]
        or not evidence["ppinfo"]
        or not evidence["setformat"]
        or exit_code != 0
        or errors
    ):
        raise RuntimeError(f"PureChat Axolotl-PM 5.0.0 startup smoke failed:\n{content[-12000:]}")
