<?php

use EthanJenkins\LivewireEditorjs\Renderers\TailwindRenderer;

function twFixture(string $name): array {
    return json_decode(file_get_contents(__DIR__ . '/../fixtures/' . $name . '.json'), true);
}

it('renders an empty blocks array as empty string', function () {
    expect(TailwindRenderer::render(['blocks' => []]))->toBe('');
});

it('renders a paragraph block', function () {
    $html = TailwindRenderer::render(twFixture('paragraph'));

    expect($html)->toContain('<p class="text-base">Hello <b>world</b>!</p>');
});

it('renders header blocks at each valid level', function () {
    foreach ([1, 2, 3, 4, 5, 6] as $level) {
        $html = TailwindRenderer::render([
            'blocks' => [['type' => 'header', 'data' => ['text' => 'T', 'level' => $level]]],
        ]);

        expect($html)->toContain("<h{$level} ");
        expect($html)->toContain(">T</h{$level}>");
    }
});

it('clamps invalid header levels', function () {
    $high = TailwindRenderer::render([
        'blocks' => [['type' => 'header', 'data' => ['text' => 'T', 'level' => 99]]],
    ]);
    $low = TailwindRenderer::render([
        'blocks' => [['type' => 'header', 'data' => ['text' => 'T', 'level' => 0]]],
    ]);

    expect($high)->toContain('<h6 ');
    expect($low)->toContain('<h1 ');
});

it('renders an image with a caption', function () {
    $html = TailwindRenderer::render(twFixture('image'));

    expect($html)->toContain('<img src="https://example.com/photo.jpg"');
    expect($html)->toContain('alt="A bold photo"');
    expect($html)->toContain('<figcaption');
    expect($html)->toContain('A <b>bold</b> photo');
});

it('omits caption element when no caption', function () {
    $html = TailwindRenderer::render([
        'blocks' => [[
            'type' => 'image',
            'data' => ['file' => ['url' => 'https://example.com/a.jpg']],
        ]],
    ]);

    expect($html)->not->toContain('<figcaption');
});

it('applies image border/background/stretched classes', function () {
    $html = TailwindRenderer::render([
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
    $html = TailwindRenderer::render(twFixture('delimiter'));

    expect($html)->toContain('<hr class="my-6 border-gray-200">');
});

it('renders unordered lists with nested items', function () {
    $html = TailwindRenderer::render(twFixture('list'));

    expect($html)->toContain('<ul class="list-disc pl-6">');
    expect($html)->toContain('First item');
    expect($html)->toContain('Nested item');
    expect($html)->toContain('Second item');
});

it('renders ordered lists', function () {
    $html = TailwindRenderer::render([
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
    $html = TailwindRenderer::render([
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

    expect($html)->toContain('<input type="checkbox" disabled checked>');
    expect($html)->toContain('<input type="checkbox" disabled>');
    expect($html)->toContain('Done');
    expect($html)->toContain('Todo');
});

it('renders quotes with optional caption', function () {
    $html = TailwindRenderer::render(twFixture('quote'));

    expect($html)->toContain('<blockquote class="border-l-4 border-gray-300');
    expect($html)->toContain('To be, or not to be.');
    expect($html)->toContain('Shakespeare');

    $noCaption = TailwindRenderer::render([
        'blocks' => [[
            'type' => 'quote',
            'data' => ['text' => 'Hi'],
        ]],
    ]);
    expect($noCaption)->not->toContain('<footer');
});

it('renders warnings with title and message', function () {
    $html = TailwindRenderer::render(twFixture('warning'));

    expect($html)->toContain('<aside class="border-l-4 border-yellow-400');
    expect($html)->toContain('Heads up');
    expect($html)->toContain('This is a warning message.');
});

it('renders tables with headings', function () {
    $html = TailwindRenderer::render(twFixture('table'));

    expect($html)->toContain('<table class="w-full border-collapse">');
    expect($html)->toContain('<th class="border p-2 text-left">Name</th>');
    expect($html)->toContain('<td class="border p-2">Alice</td>');
    expect($html)->toContain('<td class="border p-2">10</td>');
});

it('renders tables without headings', function () {
    $html = TailwindRenderer::render([
        'blocks' => [[
            'type' => 'table',
            'data' => [
                'withHeadings' => false,
                'content' => [['a', 'b'], ['c', 'd']],
            ],
        ]],
    ]);

    expect($html)->not->toContain('<thead>');
    expect($html)->toContain('<td class="border p-2">a</td>');
    expect($html)->toContain('<td class="border p-2">d</td>');
});

it('skips unknown block types', function () {
    $html = TailwindRenderer::render(twFixture('mixed'));

    expect($html)->not->toContain('ignored');
    expect($html)->toContain('Intro');
});

it('sanitizes dangerous HTML', function () {
    $html = TailwindRenderer::render([
        'blocks' => [[
            'type' => 'paragraph',
            'data' => ['text' => '<script>alert(1)</script>Hi'],
        ]],
    ]);

    expect($html)->not->toContain('<script>');
    expect($html)->toContain('Hi');
});

it('preserves allowed inline markup', function () {
    $html = TailwindRenderer::render([
        'blocks' => [[
            'type' => 'paragraph',
            'data' => ['text' => '<b>bold</b> <i>italic</i>'],
        ]],
    ]);

    expect($html)->toContain('<b>bold</b>');
    expect($html)->toContain('<i>italic</i>');
});
