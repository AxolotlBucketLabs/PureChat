# Changelog

## 2.1.0 - maintained API 5 baseline

- Continued the original PureChat plugin under NhanAZ-Plugins.
- Targeted Axolotl-PM API 5 while preserving the `PureChat` identity and PurePerms integration.
- Added typed chat formatter, event listener, configuration migration, and formatting paths for PHPStan Level Max.
- Added English and Vietnamese message resources selectable with `language: eng` or `language: vie`.
- Added target-player and console support for prefix/suffix commands, with `/giveprefix` and `/givesuffix` aliases.
- Restored optional faction tags for PiggyFactions, FactionsPro, and XeviousPE-Factions.
- Added optional event-driven ScoreHud tags without a hard ScoreHud dependency.
- Moved locale files to `resources/lang/` and added all 14 requested ISO 639-2 locales.
- Added reproducible DevTools PHAR builds and CI static analysis with pinned PurePerms source.
- No automatic GitHub release or tag is created by CI.
