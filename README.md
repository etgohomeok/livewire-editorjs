# livewire-editorjs

[Editor.js](https://editorjs.io/) component for Laravel Livewire 4, as a drop-in `<livewire:editorjs>` component with `wire:model` support. Includes a number of common blocks (including image uploads) and HTML renderers for Tailwind, Flux, and basic HTML.

## Requirements

- PHP 8.2+
- Laravel 12
- Livewire 4

## Installation

```bash
composer require etgohomeok/livewire-editorjs
```

If you're using the **Flux renderer**, you also need Flux installed in your host app:

```bash
composer require livewire/flux
```

## Usage

### Editor component

Drop it into any Livewire component's view and bind with `wire:model`:

```blade
<livewire:editorjs wire:model.live="content" />
```

`$content` on the parent component will be an array shaped like `['blocks' => [...]]` — the Editor.js save format.

### Props

Properties can be used to customize the editor on a per-use basis:

```blade
{{-- Restrict the toolbar --}}
<livewire:editorjs wire:model="post" :tools="['header', 'paragraph', 'list']" />

{{-- Different storage target --}}
<livewire:editorjs wire:model="post" disk="s3" directory="blog/images" />

{{-- Read-only preview --}}
<livewire:editorjs :value="$post" :read-only="true" />
```

### Rendering saved JSON

Pick whichever renderer suits your frontend:

```php
use EthanJenkins\LivewireEditorjs\Renderers\HtmlRenderer;
use EthanJenkins\LivewireEditorjs\Renderers\TailwindRenderer;
use EthanJenkins\LivewireEditorjs\Renderers\FluxRenderer;

$html = HtmlRenderer::render($post->content);     // vanilla HTML
$html = TailwindRenderer::render($post->content); // HTML + Tailwind utility classes
$html = FluxRenderer::render($post->content);     // Flux components
```

All three accept the raw array saved by the editor and return an HTML string. Use `{!! $html !!}` in Blade to output it — the renderers already sanitize inline markup, so double-escaping strips valid `<b>`, `<i>`, etc.

Need something different? Extend `BaseRenderer` and override the `renderParagraph`, `renderHeader`, etc. methods, or publish the source (see below) and own the code.

## Bundled Editor.js plugins

The bundled JS (`dist/editor.js`) ships with these tools:

- `header` — [@editorjs/header](https://github.com/editor-js/header) `2.8.8`
- `image` — [@editorjs/image](https://github.com/editor-js/image) `2.10.3`
- `delimiter` — [@editorjs/delimiter](https://github.com/editor-js/delimiter) `1.4.2`
- `list` — [@editorjs/list](https://github.com/editor-js/list) `2.0.9`
- `quote` — [@editorjs/quote](https://github.com/editor-js/quote) `2.7.6`
- `warning` — [@editorjs/warning](https://github.com/editor-js/warning) `1.4.1`
- `table` — [@editorjs/table](https://github.com/editor-js/table) `2.4.5`

Plus `paragraph`, which is Editor.js's implicit default block.

Core: [@editorjs/editorjs](https://github.com/codex-team/editor.js) `2.31.6`.

All seven plugins are always present in the bundle; the `tools` prop only controls which get *registered* on each editor instance.

## Configuration

Publish the config to change global defaults:

```bash
php artisan vendor:publish --tag=livewire-editorjs-config
```

```php
// config/livewire-editorjs.php
return [
    'disk' => env('LIVEWIRE_EDITORJS_DISK', 'public'),
    'directory' => env('LIVEWIRE_EDITORJS_DIRECTORY', 'editorjs-uploads'),
    'tools' => ['header', 'paragraph', 'image', 'delimiter', 'list', 'quote', 'warning', 'table'],
    'asset_route' => '/livewire-editorjs/editor.js',
];
```

Per-instance props override these.

### Publishable tags

```bash
# Customize the editor's Blade view
php artisan vendor:publish --tag=livewire-editorjs-views

# Fork the renderer classes into app/Renderers/
php artisan vendor:publish --tag=livewire-editorjs-renderers
```

## Image uploads

The `image` tool is wired to Livewire's `WithFileUploads`. Uploaded files land on the configured `disk` under `directory`, and the returned URL comes from `Storage::disk($disk)->url($path)` — so S3, R2, local `public`, etc. all work as long as the disk is configured in `config/filesystems.php`.

"Upload by URL" fetches the remote file and re-uploads it to the same disk. There's no built-in size/type validation beyond Livewire's defaults; add a policy in your host app if you need one.
