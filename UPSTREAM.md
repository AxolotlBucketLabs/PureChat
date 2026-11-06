# Upstream and maintenance policy

## Provenance

- Original project: [poggit-orphanage/PureChat](https://github.com/poggit-orphanage/PureChat).
- Archived plugin page: [Poggit Archive PureChat](https://poggit-archive.github.io/plugins/204-purechat/).
- API 5 reference port: [DemonicDev/PureChat](https://github.com/DemonicDev/PureChat).
- Maintained dependency: [AxolotlBucketLabs/PurePerms](https://github.com/AxolotlBucketLabs/PurePerms).
- Maintained repository: [AxolotlBucketLabs/PureChat](https://github.com/AxolotlBucketLabs/PureChat).

The API 5 fork is used as a reference for the manifest and chat-event migration. This repository does not rename the plugin to a fork-specific identity and does not copy unrelated reworks that change commands, permissions, or configuration semantics.

## Compatibility policy

PureChat targets Axolotl-PM API 5 and depends on the maintained PurePerms plugin. The manifest intentionally remains `PureChat` with the original namespace, command names, permission nodes, and `PurePerms` dependency.

Changes should preserve:

- `resources/config.yml` and its migration behavior;
- chat format, prefix, suffix, nametag, and multi-world settings;
- public method names used by server plugins and the PurePerms event bridge.

## Build and release policy

CI pins DevTools, Axolotl-PM, setup-php-action, GitHub Actions, and the PurePerms dependency to full commit SHAs. CI uploads a PHAR artifact but does not publish a release. A tag is allowed only after runtime checks with both the API-floor server and the current pinned server have been reviewed.
