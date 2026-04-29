<div>
    <x-dialog-modal wire:model.live="isOpen">
        <x-slot name="title">
            {{ __('Preview Note') }}: {{ $note ? ($note->title ?? __('Untitled Note')) : '' }}
        </x-slot>

        <x-slot name="content">
            <div class="mt-4 p-6 bg-white rounded border border-gray-200 overflow-y-auto max-h-[60vh] prose prose-sm max-w-none prose-indigo">
                {!! $markdownContent !!}
            </div>
        </x-slot>

        <x-slot name="footer">
            <x-secondary-button wire:click="$set('isOpen', false)" wire:loading.attr="disabled">
                {{ __('Close') }}
            </x-secondary-button>
        </x-slot>
    </x-dialog-modal>
</div>
