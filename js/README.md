# Building the bundle

This directory builds `../dist/editor.js`, which is served to browsers by the package's asset route.

## Build

```bash
cd js
npm install
npm run build
```

Output: `../dist/editor.js`. Commit it to the repo after building.

## When to rebuild

- After bumping any `@editorjs/*` version in `package.json`.
- After editing `src/index.js`.
- Before tagging a new release.

## Bundle contents

- `@editorjs/editorjs` (core)
- 7 plugins: Header, Image, Delimiter, List, Quote, Warning, Table

Plus `window.LivewireEditorjs.init(...)` as the exposed API.
