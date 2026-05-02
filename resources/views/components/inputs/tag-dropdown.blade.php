@props(['tagSearch', 'availableTags', 'selectedTags'])

@if(!empty($tagSearch) || count($availableTags) > 0)
    <div class="absolute z-10 mt-1 w-full bg-white shadow-lg max-h-60 rounded-md py-1 text-base ring-1 ring-black ring-opacity-5 overflow-auto sm:text-sm border border-gray-200">
        @foreach($availableTags as $availableTag)
            <div wire:click="addTag('{{ $availableTag }}')" class="cursor-pointer select-none relative py-2 pl-3 pr-9 hover:bg-indigo-60 text-gray-900 hover:text-indigo-900 hover:bg-indigo-50">
                {{ $availableTag }}
            </div>
        @endforeach
        
        @if(!empty($tagSearch) && !in_array(trim(strtolower($tagSearch)), $availableTags) && !in_array(trim(strtolower($tagSearch)), $selectedTags))
            <div wire:click="addTag('{{ $tagSearch }}')" class="cursor-pointer select-none relative py-2 pl-3 pr-9 hover:bg-indigo-60 text-indigo-700 hover:text-indigo-900 hover:bg-indigo-50 font-medium border-t border-gray-100">
                {{ __('Create new') }}: "{{ trim(strtolower($tagSearch)) }}"
            </div>
        @endif
    </div>
@endif
