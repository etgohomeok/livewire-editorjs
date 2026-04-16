<?php

namespace EthanJenkins\LivewireEditorjs\Livewire;

use Composer\InstalledVersions;
use Illuminate\Support\Facades\Storage;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Modelable;
use Livewire\Component;
use Livewire\WithFileUploads;

class Editorjs extends Component {
    use WithFileUploads;

    #[Modelable]
    public ?array $value = null;

    public ?string $disk = null;

    public ?string $directory = null;

    public ?array $tools = null;

    public ?string $placeholder = null;

    public bool $readOnly = false;

    public $photo;

    public function mount(): void {
        $this->disk ??= config('livewire-editorjs.disk');
        $this->directory ??= config('livewire-editorjs.directory');
        $this->tools ??= config('livewire-editorjs.tools');

        if ($this->value === null) {
            $this->value = ['blocks' => []];
        }
    }

    public function storeUploadedImage(): string {
        $path = $this->photo->store($this->directory, $this->disk);

        return Storage::disk($this->disk)->url($path);
    }

    public function uploadImageByUrl(string $url): string {
        $contents = file_get_contents($url);
        $extension = pathinfo(parse_url($url, PHP_URL_PATH), PATHINFO_EXTENSION) ?: 'jpg';
        $filename = uniqid() . '.' . $extension;
        $path = $this->directory . '/' . $filename;

        Storage::disk($this->disk)->put($path, $contents);

        return Storage::disk($this->disk)->url($path);
    }

    #[Computed]
    public function assetUrl(): string {
        $version = InstalledVersions::isInstalled('etgohomeok/livewire-editorjs')
            ? InstalledVersions::getVersion('etgohomeok/livewire-editorjs')
            : 'dev';

        return route('livewire-editorjs.asset') . '?v=' . urlencode($version);
    }

    public function render() {
        return view('livewire-editorjs::livewire.editorjs');
    }
}
