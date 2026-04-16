<div>
    <div wire:ignore>
        <div id="editorjs-{{ $this->getId() }}" class="prose max-w-none"></div>
    </div>

    @assets
    <script src="{{ $this->assetUrl }}"></script>
    @endassets

    @script
    <script>
        window.LivewireEditorjs.init({
            holder: 'editorjs-{{ $this->getId() }}',
            component: $wire,
            tools: @json($tools),
            placeholder: @json($placeholder),
            readOnly: @json($readOnly),
            initialData: @json($value),
        });
    </script>
    @endscript
</div>
