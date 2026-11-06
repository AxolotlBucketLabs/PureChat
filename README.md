# PureChat

PureChat formats player chat, prefixes, suffixes and nametags for PocketMine-MP and Axolotl-PM API 5 servers.

[![CI](https://github.com/AxolotlBucketLabs/PureChat/actions/workflows/build.yml/badge.svg)](https://github.com/AxolotlBucketLabs/PureChat/actions/workflows/build.yml) [![Latest release](https://img.shields.io/github/v/release/AxolotlBucketLabs/PureChat)](https://github.com/AxolotlBucketLabs/PureChat/releases/latest) [![License](https://img.shields.io/github/license/AxolotlBucketLabs/PureChat)](LICENSE) [![Discord](https://img.shields.io/discord/986553214889517088?label=Discord&logo=discord&logoColor=white&color=5865F2)](https://discord.gg/j2X83ujT6c)

## Compatibility

- The plugin targets API 5 and is tested against Axolotl-PM 5.51.1 in CI.
- PHP 8.4 is used by the development and build workflow.
- PurePerms is required.
- FactionsPro, XeviousPE-Factions, PiggyFactions and ScoreHud are optional integrations.

## Installation

1. Download the `PureChat.phar` file from the [latest release](https://github.com/AxolotlBucketLabs/PureChat/releases/latest).
2. Install PurePerms in the same server.
3. Copy both PHAR files to the server's `plugins/` directory.
4. Start the server and edit `plugin_data/PureChat/config.yml`.

PureChat creates its configuration on the first start. Set `language: vie` to use the Vietnamese translation. The available locale files are in `resources/lang/`.

## Commands

| Command | Description | Permission |
| --- | --- | --- |
| `/setsuffix <suffix>` | Set your own suffix. Use `{BLANK}` for spaces. | `pchat.command.setsuffix` |
| `/setsuffix <player> <suffix>` | Set another player's suffix. `/givesuffix` is an alias. | `pchat.command.setsuffix` |
| `/setprefix <prefix>` | Set your own prefix. Use `{BLANK}` for spaces. | `pchat.command.setprefix` |
| `/setprefix <player> <prefix>` | Set another player's prefix. `/giveprefix` is an alias. | `pchat.command.setprefix` |
| `/setnametag <group> <world> <format>` | Set a group's default nametag. | `pchat.command.setnametag` |
| `/setformat <group> <world> <format>` | Set a group's default chat format. | `pchat.command.setformat` |

## Integrations

Set `default-factions-plugin` in `config.yml` to select a faction provider, or leave it `null` for auto-detection. PureChat supports the legacy FactionsPro and XeviousPE-Factions adapters as well as PiggyFactions.

When ScoreHud is installed, PureChat exposes `purechat.prefix`, `purechat.suffix`, `pureperms.group`, `pureperms.rank`, `purechat.faction_name` and `purechat.faction_rank` through its tag event.

## Build locally

The repository uses [AxolotlBucketLabs DevTools](https://github.com/AxolotlBucketLabs/DevTools) for PHAR packaging and PHPStan level max analysis. The local build requires a checkout of PurePerms and an Axolotl-PM source tree, as shown in the workflow.

```powershell
php -d phar.readonly=0 .tooling/DevTools/bin/devtools.php build --project=. --out=build --overwrite --json
```

The build workflow produces a PHAR artifact for each push and pull request. It does not publish a GitHub release automatically.

## Provenance

See [UPSTREAM.md](UPSTREAM.md) for the original project, retained attribution and compatibility policy. See [CHANGELOG.md](CHANGELOG.md) for maintained changes.
