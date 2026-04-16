<?php

use EthanJenkins\LivewireEditorjs\Renderers\FluxRenderer;

class FluxRendererRaw extends FluxRenderer {
    protected static function finalize(string $html): string {
        return $html;
    }
}

function fluxFixture(string $name): array {
    return json_decode(file_get_contents(__DIR__ . '/../fixtures/' . $name . '.json'), true);
}

it('renders an empty blocks array as empty string', function () {
    expect(FluxRendererRaw::render(['blocks' => []]))->toBe('');
});

it('renders a paragraph block', function () {
    $html = FluxRendererRaw::render(fluxFixture('paragraph'));

    expect($html)->toContain('<flux:text>Hello <b>world</b>!</flux:text>');
});

it('renders header blocks at each valid level', function () {
    foreach ([1, 2, 3, 4] as $level) {
        $html = FluxRendererRaw::render([
            'blocks' => [['type' => 'header', 'data' => ['text' => 'T', 'level' => $level]]],
        ]);

        expect($html)->toContain("<flux:heading level=\"{$level}\"");
        expect($html)->toContain('>T</flux:heading>');
    }
});

it('clamps invalid header levels to 4', function () {
    $html = FluxRendererRaw::render([
        'blocks' => [['type' => 'header', 'data' => ['text' => 'T', 'level' => 99]]],
    ]);

    expect($html)->toContain('<flux:heading level="4"');
});

it('clamps header level 0 to 1', function () {
    $html = FluxRendererRaw::render([
        'blocks' => [['type' => 'header', 'data' => ['text' => 'T', 'level' => 0]]],
    ]);

    expect($html)->toContain('<flux:heading level="1"');
});

it('renders an image with a caption', function () {
    $html = FluxRendererRaw::render(fluxFixture('image'));

    expect($html)->toContain('<img src="https://example.com/photo.jpg"');
    expect($html)->toContain('alt="A bold photo"');
    expect($html)->toContain('<figcaption><flux:text');
    expect($html)->toContain('A <b>bold</b> photo');
});

it('omits caption element when no caption', function () {
    $html = FluxRendererRaw::render([
        'blocks' => [[
            'type' => 'image',
            'data' => ['file' => ['url' => 'https://example.com/a.jpg']],
        ]],
    ]);

    expect($html)->not->toContain('<figcaption');
});

it('applies image border/background/stretched classes', function () {
    $html = FluxRendererRaw::render([
        'blocks' => [[
            'type' => 'image',
            'data' => [
                'file' => ['url' => 'https://example.com/a.jpg'],
                'withBorder' => true,
                'withBackground' => true,
                'stretched' => true,
            ],
        ]],
    ]);

    expect($html)->toContain('border border-gray-200 rounded');
    expect($html)->toContain('bg-gray-100 p-4');
    expect($html)->toContain('w-full');
});

it('renders delimiter', function () {
    $html = FluxRendererRaw::render(fluxFixture('delimiter'));

    expect($html)->toContain('<flux:separator class="my-6" />');
});

it('renders unordered lists with nested items', function () {
    $html = FluxRendererRaw::render(fluxFixture('list'));

    expect($html)->toContain('<ul class="list-disc pl-6">');
    expect($html)->toContain('First item');
    expect($html)->toContain('Nested item');
    expect($html)->toContain('Second item');
});

it('renders ordered lists', function () {
    $html = FluxRendererRaw::render([
        'blocks' => [[
            'type' => 'list',
            'data' => [
                'style' => 'ordered',
                'items' => [['content' => 'A', 'items' => []]],
            ],
        ]],
    ]);

    expect($html)->toContain('<ol class="list-decimal pl-6">');
    expect($html)->toContain('A');
});

it('renders checklists with checked state', function () {
    $html = FluxRendererRaw::render([
        'blocks' => [[
            'type' => 'list',
            'data' => [
                'style' => 'checklist',
                'items' => [
                    ['content' => 'Done', 'meta' => ['checked' => true]],
                    ['content' => 'Todo', 'meta' => ['checked' => false]],
                ],
            ],
        ]],
    ]);

    expect($html)->toContain('<flux:checkbox.group>');
    expect($html)->toContain('label="Done"');
    expect($html)->toContain(' checked disabled');
    expect($html)->toContain('label="Todo"');
});

it('renders quotes with optional caption', function () {
    $html = FluxRendererRaw::render(fluxFixture('quote'));

    expect($html)->toContain('<blockquote class="border-l-4 border-gray-300');
    expect($html)->toContain('To be, or not to be.');
    expect($html)->toContain('Shakespeare');

    $noCaption = FluxRendererRaw::render([
        'blocks' => [[
            'type' => 'quote',
            'data' => ['text' => 'Hi'],
        ]],
    ]);
    expect($noCaption)->not->toContain('<footer');
});

it('renders warnings with title and message', function () {
    $html = FluxRendererRaw::render(fluxFixture('warning'));

    expect($html)->toContain('<flux:callout color="yellow"');
    expect($html)->toContain('<flux:callout.heading>Heads up</flux:callout.heading>');
    expect($html)->toContain('<flux:callout.text>This is a warning message.</flux:callout.text>');
});

it('renders tables with headings', function () {
    $html = FluxRendererRaw::render(fluxFixture('table'));

    expect($html)->toContain('<flux:table>');
    expect($html)->toContain('<flux:table.columns>');
    expect($html)->toContain('<flux:table.column>Name</flux:table.column>');
    expect($html)->toContain('<flux:table.cell>Alice</flux:table.cell>');
    expect($html)->toContain('<flux:table.cell>10</flux:table.cell>');
});

it('renders tables without headings', function () {
    $html = FluxRendererRaw::render([
        'blocks' => [[
            'type' => 'table',
            'data' => [
                'withHeadings' => false,
                'content' => [['a', 'b'], ['c', 'd']],
            ],
        ]],
    ]);

    expect($html)->not->toContain('<flux:table.columns>');
    expect($html)->toContain('<flux:table.cell>a</flux:table.cell>');
    expect($html)->toContain('<flux:table.cell>d</flux:table.cell>');
});

it('skips unknown block types', function () {
    $html = FluxRendererRaw::render(fluxFixture('mixed'));

    expect($html)->not->toContain('ignored');
    expect($html)->toContain('Intro');
});

it('sanitizes dangerous HTML', function () {
    $html = FluxRendererRaw::render([
        'blocks' => [[
            'type' => 'paragraph',
            'data' => ['text' => '<script>alert(1)</script>Hi'],
        ]],
    ]);

    expect($html)->not->toContain('<script>');
    expect($html)->toContain('Hi');
});

it('preserves allowed inline markup', function () {
    $html = FluxRendererRaw::render([
        'blocks' => [[
            'type' => 'paragraph',
            'data' => ['text' => '<b>bold</b> <i>italic</i>'],
        ]],
    ]);

    expect($html)->toContain('<b>bold</b>');
    expect($html)->toContain('<i>italic</i>');
});
