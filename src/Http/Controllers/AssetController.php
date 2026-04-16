<?php

namespace EthanJenkins\LivewireEditorjs\Http\Controllers;

use Illuminate\Routing\Controller;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

class AssetController extends Controller {
    public function show(): BinaryFileResponse {
        $path = __DIR__ . '/../../../dist/editor.js';

        return response()->file($path, [
            'Content-Type' => 'application/javascript',
            'Cache-Control' => 'public, max-age=31536000, immutable',
        ]);
    }
}
