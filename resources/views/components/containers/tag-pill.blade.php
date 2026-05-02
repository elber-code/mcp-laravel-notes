@props(['tag'])

<span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-indigo-100 text-indigo-800">
    {{ $tag }}
    <button type="button" wire:click="removeTag('{{ $tag }}')" class="flex-shrink-0 ml-1.5 h-4 w-4 rounded-full inline-flex items-center justify-center text-indigo-400 hover:bg-indigo-200 hover:text-indigo-500 focus:outline-none focus:bg-indigo-500 focus:text-white">
        <span class="sr-only">Remove tag</span>
        <x-svgs.x-mark class="h-2 w-2" />
    </button>
</span>
