<div>
    <x-dialog-modal wire:model.live="isOpen">
        <x-slot name="title">
            {{ __('Edit Key Note') }}
        </x-slot>

        <x-slot name="content">
            <div class="space-y-4">
                <div>
                    <x-label for="edit-key" value="{{ __('Key (Unique String)') }}" />
                    <x-input id="edit-key" type="text" class="mt-1 block w-full" wire:model="key" autofocus />
                    <x-input-error for="key" class="mt-2" />
                </div>

                <div>
                    <x-label for="edit-title" value="{{ __('Title (Optional)') }}" />
                    <x-input id="edit-title" type="text" class="mt-1 block w-full" wire:model="title" />
                    <x-input-error for="title" class="mt-2" />
                </div>

                <div>
                    <x-label for="edit-content" value="{{ __('Content') }}" />
                    <textarea id="edit-content" wire:model="content" rows="6" class="mt-1 block w-full border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm"></textarea>
                    <x-input-error for="content" class="mt-2" />
                </div>
            </div>
        </x-slot>

        <x-slot name="footer">
            <x-secondary-button wire:click="$set('isOpen', false)" wire:loading.attr="disabled">
                {{ __('Cancel') }}
            </x-secondary-button>

            <x-button class="ms-3 bg-emerald-600 hover:bg-emerald-700" wire:click="save" wire:loading.attr="disabled">
                {{ __('Save Changes') }}
            </x-button>
        </x-slot>
    </x-dialog-modal>
</div>
