<div>
    <x-dialog-modal wire:model.live="isOpen" :maxWidth="$isFullscreen ? 'full' : '2xl'">
        <x-slot name="title">
            <div class="flex justify-between items-center w-full">
                <span>{{ __('Preview Note') }}: {{ $note ? ($note->title ?? __('Untitled Note')) : '' }}</span>
                <button wire:click="$toggle('isFullscreen')" class="text-gray-400 hover:text-gray-600 focus:outline-none" title="{{ $isFullscreen ? __('Shrink') : __('Expand') }}">
                    @if($isFullscreen)
                        <x-svgs.shrink class="w-5 h-5" />
                    @else
                        <x-svgs.expand class="w-5 h-5" />
                    @endif
                </button>
            </div>
        </x-slot>

        <x-slot name="content">
            <div class="mt-4 p-6 bg-white rounded border border-gray-200 overflow-y-auto {{ $isFullscreen ? 'max-h-[80vh]' : 'max-h-[60vh]' }} prose prose-sm max-w-none prose-indigo">
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
