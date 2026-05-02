@props(['selectedTags', 'isAddingTag', 'tagSearch', 'availableTags'])

<!-- Tags Section -->
<div>
    <x-label value="{{ __('Tags') }}" class="mb-2" />
    
    <div class="flex flex-wrap items-center gap-2">
        <!-- Selected Tags Pills -->
        @foreach($selectedTags as $tag)
            <x-containers.tag-pill :tag="$tag" />
        @endforeach

        <!-- Add Tag Button -->
        @if(!$isAddingTag)
            <button type="button" wire:click="toggleAddTag" class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-gray-100 text-gray-800 hover:bg-gray-200 focus:outline-none transition-colors">
                <x-svgs.plus class="w-3 h-3 mr-1" />
                {{ __('Add') }}
            </button>
        @endif
    </div>

    <!-- Add Tag Input Area -->
    @if($isAddingTag)
        <div class="mt-3 relative">
            <div class="flex">
                <x-input type="text" wire:model.live.debounce.150ms="tagSearch" wire:keydown.enter.prevent="addTag(tagSearch)" placeholder="{{ __('Type a tag...') }}" class="block w-full sm:text-sm rounded-md" autofocus />
                <x-secondary-button wire:click="toggleAddTag" class="ml-2">
                    {{ __('Cancel') }}
                </x-secondary-button>
            </div>
            
            <x-inputs.tag-dropdown :tagSearch="$tagSearch" :availableTags="$availableTags" :selectedTags="$selectedTags" />
        </div>
    @endif
</div>
