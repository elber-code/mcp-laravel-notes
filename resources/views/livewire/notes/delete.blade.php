<div>
    <x-confirmation-modal wire:model.live="isOpen">
        <x-slot name="title">
            {{ __('Delete Note') }}
        </x-slot>

        <x-slot name="content">
            {{ __('Are you sure you would like to delete this note? This action cannot be undone.') }}
            
            @if($note)
            <div class="mt-4 p-4 bg-gray-50 rounded border border-gray-200">
                <p class="font-semibold text-gray-700 truncate">{{ $note->title ?? __('Untitled Note') }}</p>
            </div>
            @endif
        </x-slot>

        <x-slot name="footer">
            <x-secondary-button wire:click="$set('isOpen', false)" wire:loading.attr="disabled">
                {{ __('Cancel') }}
            </x-secondary-button>

            <x-danger-button class="ms-3" wire:click="delete" wire:loading.attr="disabled">
                {{ __('Delete') }}
            </x-danger-button>
        </x-slot>
    </x-confirmation-modal>
</div>
