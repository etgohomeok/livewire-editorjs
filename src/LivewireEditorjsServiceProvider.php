<?php

namespace EthanJenkins\LivewireEditorjs;

use EthanJenkins\LivewireEditorjs\Http\Controllers\AssetController;
use EthanJenkins\LivewireEditorjs\Livewire\Editorjs;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\ServiceProvider;
use Livewire\Livewire;

class LivewireEditorjsServiceProvider extends ServiceProvider {
    public function register(): void {
        $this->mergeConfigFrom(
            __DIR__ . '/../config/livewire-editorjs.php',
            'livewire-editorjs'
        );
    }

    public function boot(): void {
        $this->loadViewsFrom(__DIR__ . '/../resources/views', 'livewire-editorjs');

        Livewire::component('editorjs', Editorjs::class);

        Route::get(
            config('livewire-editorjs.asset_route'),
            [AssetController::class, 'show']
        )->name('livewire-editorjs.asset');

        if ($this->app->runningInConsole()) {
            $this->publishes([
                __DIR__ . '/../config/livewire-editorjs.php' => config_path('livewire-editorjs.php'),
            ], 'livewire-editorjs-config');

            $this->publishes([
                __DIR__ . '/../resources/views' => resource_path('views/vendor/livewire-editorjs'),
            ], 'livewire-editorjs-views');

            $this->publishes([
                __DIR__ . '/Renderers' => app_path('Renderers'),
            ], 'livewire-editorjs-renderers');
        }
    }
}
