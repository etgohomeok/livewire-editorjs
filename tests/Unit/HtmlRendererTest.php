<?php

use EthanJenkins\LivewireEditorjs\Renderers\HtmlRenderer;

function htmlFixture(string $name): array {
    return json_decode(file_get_contents(__DIR__ . '/../fixtures/' . $name . '.json'), true);
}

it('renders an empty blocks array as empty string', function () {
    expect(HtmlRenderer::render(['blocks' => []]))->toBe('');
});

it('renders a paragraph block', function () {
    $html = HtmlRenderer::render(htmlFixture('paragraph'));

    expect($html)->toBe('<p>Hello <b>world</b>!</p>');
});

it('renders header blocks at each valid level', function () {
    foreach ([1, 2, 3, 4, 5, 6] as $level) {
        $html = HtmlRenderer::render([
            'blocks' => [
                ['type' => 'header', 'data' => ['text' => 'Title', 'level' => $level]],
            ],
        ]);

        expect($html)->toBe("<h{$level}>Title</h{$level}>");
    }
});

it('clamps invalid header levels', function () {
    $high = HtmlRenderer::render([
        'blocks' => [['type' => 'header', 'data' => ['text' => 'T', 'level' => 99]]],
    ]);
    $low = HtmlRenderer::render([
        'blocks' => [['type' => 'header', 'data' => ['text' => 'T', 'level' => 0]]],
    ]);

    expect($high)->toBe('<h6>T</h6>');
    expect($low)->toBe('<h1>T</h1>');
});

it('renders an image with a caption', function () {
    $html = HtmlRenderer::render(htmlFixture('image'));

    expect($html)->toContain('<figure>');
    expect($html)->toContain('<img src="https://example.com/photo.jpg"');
    expect($html)->toContain('alt="A bold photo"');
    expect($html)->toContain('<figcaption>A <b>bold</b> photo</figcaption>');
});

it('omits caption element when no caption', function () {
    $html = HtmlRenderer::render([
        'blocks' => [[
            'type' => 'image',
            'data' => ['file' => ['url' => 'https://example.com/a.jpg']],
        ]],
    ]);

    expect($html)->not->toContain('<figcaption>');
});

it('renders delimiter', function () {
    $html = HtmlRenderer::render(htmlFixture('delimiter'));

    expect($html)->toBe('<hr>');
});

it('renders unordered lists with nested items', function () {
    $html = HtmlRenderer::render(htmlFixture('list'));

    expect($html)->toContain('<ul>');
    expect($html)->toContain('<li>First item<ul><li>Nested item</li></ul></li>');
    expect($html)->toContain('<li>Second item</li>');
});

it('renders ordered lists', function () {
    $html = HtmlRenderer::render([
        'blocks' => [[
            'type' => 'list',
            'data' => [
                'style' => 'ordered',
                'items' => [['content' => 'A', 'items' => []]],
            ],
        ]],
    ]);

    expect($html)->toBe('<ol><li>A</li></ol>');
});

it('renders checklists with checked state', function () {
    $html = HtmlRenderer::render([
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
    $html = HtmlRenderer::render(htmlFixture('quote'));

    expect($html)->toContain('<blockquote>');
    expect($html)->toContain('<p>To be, or not to be.</p>');
    expect($html)->toContain('<footer>Shakespeare</footer>');

    $noCaption = HtmlRenderer::render([
        'blocks' => [[
            'type' => 'quote',
            'data' => ['text' => 'Hi'],
        ]],
    ]);
    expect($noCaption)->not->toContain('<footer>');
});

it('renders warnings with title and message', function () {
    $html = HtmlRenderer::render(htmlFixture('warning'));

    expect($html)->toContain('<strong>Heads up</strong>');
    expect($html)->toContain('<p>This is a warning message.</p>');
});

it('renders tables with headings', function () {
    $html = HtmlRenderer::render(htmlFixture('table'));

    expect($html)->toContain('<thead><tr><th>Name</th><th>Score</th></tr></thead>');
    expect($html)->toContain('<tr><td>Alice</td><td>10</td></tr>');
    expect($html)->toContain('<tr><td>Bob</td><td>20</td></tr>');
});

it('renders tables without headings', function () {
    $html = HtmlRenderer::render([
        'blocks' => [[
            'type' => 'table',
            'data' => [
                'withHeadings' => false,
                'content' => [['a', 'b'], ['c', 'd']],
            ],
        ]],
    ]);

    expect($html)->not->toContain('<thead>');
    expect($html)->toContain('<tr><td>a</td><td>b</td></tr>');
    expect($html)->toContain('<tr><td>c</td><td>d</td></tr>');
});

it('skips unknown block types', function () {
    $html = HtmlRenderer::render(htmlFixture('mixed'));

    expect($html)->not->toContain('ignored');
    expect($html)->toContain('<h1>Intro</h1>');
});

it('sanitizes dangerous HTML', function () {
    $html = HtmlRenderer::render([
        'blocks' => [[
            'type' => 'paragraph',
            'data' => ['text' => '<script>alert(1)</script>Hi'],
        ]],
    ]);

    expect($html)->not->toContain('<script>');
    expect($html)->toContain('Hi');
});

it('preserves allowed inline markup', function () {
    $html = HtmlRenderer::render([
        'blocks' => [[
            'type' => 'paragraph',
            'data' => ['text' => '<b>bold</b> <i>italic</i> <a href="/x">link</a>'],
        ]],
    ]);

    expect($html)->toContain('<b>bold</b>');
    expect($html)->toContain('<i>italic</i>');
    expect($html)->toContain('<a href="/x">link</a>');
});
