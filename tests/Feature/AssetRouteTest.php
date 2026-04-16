<?php

it('the asset route returns 200', function () {
    $this->get('/livewire-editorjs/editor.js')->assertOk();
});

it('the asset route returns javascript content-type', function () {
    $response = $this->get('/livewire-editorjs/editor.js');

    expect($response->headers->get('Content-Type'))->toBe('application/javascript');
});

it('the asset route sets immutable cache header', function () {
    $response = $this->get('/livewire-editorjs/editor.js');

    expect($response->headers->get('Cache-Control'))->toContain('immutable');
    expect($response->headers->get('Cache-Control'))->toContain('max-age=31536000');
});

it('the asset route returns the dist file content', function () {
    $response = $this->get('/livewire-editorjs/editor.js');

    $expected = file_get_contents(__DIR__ . '/../../dist/editor.js');
    expect($response->streamedContent() ?: $response->getContent())->toBe($expected);
});
