
## General guidelines

- **Bump the version on every user-visible change.** The next tag should reflect what this commit changed, not a release cadence. If the change is shipped, the tag goes with it — no "I'll bump later." Record the bump in `CHANGELOG.md` at the same time.
- **Pick the bump level by scope:**
  - **MAJOR** — anything a consumer has to react to. Removed or renamed public PHP method, prop, or config key; renderer HTML output changed in a way that would visibly break existing styling; bundled Editor.js plugin dropped; raised PHP / Laravel / Livewire minimums; service-provider route path or registered component name changed.
  - **MINOR** — purely additive. New renderer method, new component prop, new bundled plugin, new config key (with a safe default), new publish tag. Existing callers keep working untouched.
  - **PATCH** — fixes with no API change. Internal renderer fixes that produce equivalent HTML, JS bundle rebuilds from upstream patch bumps, doc-only or test-only edits, tightening escaping that wasn't valid output anyway.
- **Pre-1.0:** minor bumps may still contain breaking changes. When they do, prefix the `CHANGELOG.md` entry with `BREAKING:`.
- **When a change sits between two levels, pick the higher one.** Erring toward too-loud is cheap; erring toward too-quiet breaks host apps.
- **Update `README.md` in the same commit whenever user-visible surface changes.** That includes — but is not limited to — bundled Editor.js plugin versions (list the *resolved* version from `js/package-lock.json`, not the `^caret` range from `package.json`), new or removed component props, new or removed config keys, new publish tags, renderer class names, PHP / Laravel / Livewire minimums, and the list of bundled plugins. If the change isn't in the README, host-app developers won't discover it.

## Build, test, release

**Build the JS bundle** (from `js/`): `npm install` first time, then `npm run build` writes `../dist/editor.js`. Rebuild and commit `dist/editor.js` when any `@editorjs/*` version bumps, when `js/src/index.js` changes, or before tagging a release. Expected bundle size: ~385 KB (~100 KB gzipped).

**Run tests**: `composer install` first time, then `./vendor/bin/pest`. Fast feedback: `./vendor/bin/pest --filter=HtmlRenderer` (or `Tailwind`, `Flux`, `AssetRoute`, `Editorjs`). Don't edit `specs/`.

**Release**: bump anything that changed → rebuild JS and commit `dist/editor.js` if `js/` was touched → `composer validate --strict` clean → `./vendor/bin/pest` green → update `CHANGELOG.md` → `git tag vX.Y.Z && git push --tags`. No `composer publish` step — Packagist pulls from the git tag via webhook (first-time registration at https://packagist.org/packages/etgohomeok/livewire-editorjs needs a manual submit).

## Conventions

- Package name: `etgohomeok/livewire-editorjs` (composer) / `EthanJenkins\LivewireEditorjs` (namespace).
- `livewire/flux` is NOT a require — `FluxRenderer` only emits Flux component tags; the host app supplies Flux if it uses that renderer.