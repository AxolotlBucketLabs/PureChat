# PureChat

PureChat is a maintained Axolotl-PM API 5 continuation that provides group-based chat formats, prefixes, suffixes, and nametags through PurePerms. This repository is maintained under [NhanAZ-Plugins](https://github.com/NhanAZ-Plugins) and keeps the original `PureChat` identity, `PurePerms` dependency, commands, permission nodes, and configuration layout.

## Compatibility

- Axolotl-PM API 5 (`api: [5.0.0]`), tested against the pinned 5.51.1 source in CI.
- Requires the maintained [PurePerms](https://github.com/NhanAZ-Plugins/PurePerms) plugin.
- PHP 8.4 for the development and build toolchain.

Set `language: vi` in `config.yml` to use Vietnamese messages. The default language is English (`en`).

The maintained baseline is `2.1.0`. CI produces a PHAR artifact for every push and pull request; it does not publish a GitHub release automatically.

See [UPSTREAM.md](UPSTREAM.md) for provenance and compatibility policy, and [CHANGELOG.md](CHANGELOG.md) for maintained changes.


# Commands

Command | Description | Permission
--- | --- | ---
`/setsuffix <suffix>` | Set your suffix in-game. | pchat.command.setsuffix
`/setprefix <prefix>` | Set your prefix in-game. | pchat.command.setprefix
`/setnametag <group> <world> <format>` | Set the default nametag of a group | pchat.command.setnametag
`/setformat <group> <world> <format>` | Set default chat format | pchat.command.setformat


# FAQ

**Why can't players have coloured chat?**

You need to set 'pchat.coloredMessages' permission for the players if you want to allow them to use colored messages

**What are the colour codes to use, and where should I put them?**

See the available colour codes listed in the Config. Do not use colour codes in PurePerms groups directly; use PureChat config instead.

# Permissions

 - pchat
 - pchat.coloredMessages
 - pchat.command
 - pchat.command.setsuffix
 - pchat.command.setprefix
 - pchat.command.setnametag
 - pchat.command.setformat

## Build locally

The repository uses [NhanAZ/DevTools](https://github.com/NhanAZ/DevTools) for reproducible PHAR builds and PHPStan Level Max analysis. A local build from the workspace can be run with:

```powershell
php -d phar.readonly=0 .tooling/DevTools/bin/devtools.php build --project=. --out=build --overwrite --json
```

The PHPStan configuration also needs the PurePerms source and an Axolotl-PM checkout, as shown in CI.
