<?php

return [
    'disk' => env('LIVEWIRE_EDITORJS_DISK', 'public'),
    'directory' => env('LIVEWIRE_EDITORJS_DIRECTORY', 'editorjs-uploads'),

    'tools' => [
        'header',
        'paragraph',
        'image',
        'delimiter',
        'list',
        'quote',
        'warning',
        'table',
    ],

    'asset_route' => '/livewire-editorjs/editor.js',
];
