<div>
    <x-dialog-modal wire:model.live="isOpen">
        <x-slot name="title">
            {{ __('Create Note') }}
        </x-slot>

        <x-slot name="content">
            <div class="space-y-4">
                <div>
                    <x-label for="title" value="{{ __('Title (Optional)') }}" />
                    <x-input id="title" type="text" class="mt-1 block w-full" wire:model="title" autofocus />
                    <x-input-error for="title" class="mt-2" />
                </div>

                <x-inputs.tags-selector 
                    :selectedTags="$selectedTags" 
                    :isAddingTag="$isAddingTag" 
                    :tagSearch="$tagSearch" 
                    :availableTags="$availableTags" 
                />

                <div>
                    <x-label for="content" value="{{ __('Content') }}" />
                    <textarea id="content" wire:model="content" rows="6" class="mt-1 block w-full border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm"></textarea>
                    <x-input-error for="content" class="mt-2" />
                </div>
            </div>
        </x-slot>

        <x-slot name="footer">
            <x-secondary-button wire:click="$set('isOpen', false)" wire:loading.attr="disabled">
                {{ __('Cancel') }}
            </x-secondary-button>

            <x-button class="ms-3" wire:click="save" wire:loading.attr="disabled">
                {{ __('Save') }}
            </x-button>
        </x-slot>
    </x-dialog-modal>
</div>
