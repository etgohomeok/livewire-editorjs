<?php

namespace EthanJenkins\LivewireEditorjs\Tests;

use EthanJenkins\LivewireEditorjs\LivewireEditorjsServiceProvider;
use Livewire\LivewireServiceProvider;
use Orchestra\Testbench\TestCase as OrchestraTestCase;

class TestCase extends OrchestraTestCase {
    protected function getPackageProviders($app): array {
        return [
            LivewireServiceProvider::class,
            LivewireEditorjsServiceProvider::class,
        ];
    }

    protected function defineEnvironment($app): void {
        $app['config']->set('app.key', 'base64:' . base64_encode(random_bytes(32)));
        $app['config']->set('filesystems.default', 'public');
        $app['config']->set('filesystems.disks.public', [
            'driver' => 'local',
            'root' => storage_path('app/public'),
            'url' => 'http://localhost/storage',
            'visibility' => 'public',
        ]);
    }
}
