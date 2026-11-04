# PureChat

PureChat is a maintained Axolotl-PM API 5 continuation that provides group-based chat formats, prefixes, suffixes, and nametags through PurePerms. This repository is maintained under [NhanAZ-Plugins](https://github.com/NhanAZ-Plugins) and keeps the original `PureChat` identity, `PurePerms` dependency, commands, permission nodes, and configuration layout.

[![CI](https://github.com/NhanAZ-Plugins/PureChat/actions/workflows/build.yml/badge.svg)](https://github.com/NhanAZ-Plugins/PureChat/actions/workflows/build.yml)
[![Latest release](https://img.shields.io/github/v/release/NhanAZ-Plugins/PureChat)](https://github.com/NhanAZ-Plugins/PureChat/releases/latest)
[![License](https://img.shields.io/github/license/NhanAZ-Plugins/PureChat)](LICENSE)
[![Discord server online members](https://img.shields.io/discord/986553214889517088?label=Discord&logo=discord&logoColor=white&color=5865F2)](https://discord.gg/j2X83ujT6c)

## Compatibility

- Axolotl-PM API 5 (`api: [5.0.0]`), tested against the pinned 5.51.1 source in CI.
- Requires the maintained [PurePerms](https://github.com/NhanAZ-Plugins/PurePerms) plugin.
- PHP 8.4 for the development and build toolchain.

Set `language: vie` in `config.yml` to use Vietnamese messages. The locale files live in `resources/lang/` and use these ISO 639-2 codes: `eng`, `vie`, `kor`, `rus`, `spa`, `ukr`, `zho`, `ind`, `tur`, `fra`, `por`, `deu`, `jpn`, and `ita`. English (`eng`) is the default.

The maintained baseline is `2.1.0`. CI produces a PHAR artifact for every push and pull request; it does not publish a GitHub release automatically.

See [UPSTREAM.md](UPSTREAM.md) for provenance and compatibility policy, and [CHANGELOG.md](CHANGELOG.md) for maintained changes.


# Commands

Command | Description | Permission
--- | --- | ---
`/setsuffix <suffix>` | Set your own suffix; use `{BLANK}` for spaces. | pchat.command.setsuffix
`/setprefix <prefix>` | Set your own prefix; use `{BLANK}` for spaces. | pchat.command.setprefix
`/setsuffix <player> <suffix>` | Set another player's suffix; `/givesuffix` is an alias. | pchat.command.setsuffix
`/setprefix <player> <prefix>` | Set another player's prefix; `/giveprefix` is an alias. | pchat.command.setprefix
`/setnametag <group> <world> <format>` | Set the default nametag of a group | pchat.command.setnametag
`/setformat <group> <world> <format>` | Set default chat format | pchat.command.setformat


# FAQ

**Why can't players have coloured chat?**

You need to set 'pchat.coloredMessages' permission for the players if you want to allow them to use colored messages

**What are the colour codes to use, and where should I put them?**

See the available colour codes listed in the Config. Do not use colour codes in PurePerms groups directly; use PureChat config instead.

**How do faction tags work?**

Set `default-factions-plugin: PiggyFactions` in `config.yml`, or leave it `null` for auto-detection. The optional integration provides `{fac_name}` and `{fac_rank}`. PureChat also recognizes the legacy FactionsPro and XeviousPE-Factions adapters.

**How do I use ScoreHud tags?**

ScoreHud is optional. When it is installed, PureChat resolves `purechat.prefix`, `purechat.suffix`, `pureperms.group`, `pureperms.rank`, `purechat.faction_name`, and `purechat.faction_rank` through ScoreHud's tag event.

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
