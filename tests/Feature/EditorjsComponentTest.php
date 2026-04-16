<?php

use EthanJenkins\LivewireEditorjs\Livewire\Editorjs;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Livewire\Livewire;

it('mounts with default config values', function () {
    Livewire::test(Editorjs::class)
        ->assertSet('disk', 'public')
        ->assertSet('directory', 'editorjs-uploads')
        ->assertSet('tools', ['header', 'paragraph', 'image', 'delimiter', 'list', 'quote', 'warning', 'table'])
        ->assertSet('value', ['blocks' => []]);
});

it('respects props over config', function () {
    Livewire::test(Editorjs::class, [
        'disk' => 's3',
        'directory' => 'blog/images',
        'tools' => ['header', 'paragraph'],
    ])
        ->assertSet('disk', 's3')
        ->assertSet('directory', 'blog/images')
        ->assertSet('tools', ['header', 'paragraph']);
});

it('binds value to a parent wire:model', function () {
    $reflection = new ReflectionProperty(Editorjs::class, 'value');
    $attributes = $reflection->getAttributes(\Livewire\Attributes\Modelable::class);

    expect($attributes)->not->toBeEmpty();
});

it('stores uploaded images on the configured disk', function () {
    Storage::fake('public');

    $file = UploadedFile::fake()->image('test.jpg');

    Livewire::test(Editorjs::class)
        ->set('photo', $file)
        ->call('storeUploadedImage');

    Storage::disk('public')->assertExists('editorjs-uploads/' . $file->hashName());
});
