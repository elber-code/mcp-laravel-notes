<div>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                {{ __('Notes') }}
            </h2>
            <x-button wire:click="$dispatch('open-create-note-modal')">
                {{ __('Create Note') }}
            </x-button>
        </div>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">

            <!-- Filters Section -->
            <div class="bg-white overflow-hidden shadow-xl sm:rounded-lg p-6 mb-6">
                <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                    <div>
                        <label for="search" class="block text-sm font-medium text-gray-700">{{ __('Search') }}</label>
                        <input wire:model.live.debounce.300ms="search" type="text" id="search" class="mt-1 block w-full border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm" placeholder="{{ __('Search title or content...') }}">
                    </div>
                    <div>
                        <label for="dateFrom" class="block text-sm font-medium text-gray-700">{{ __('Date From') }}</label>
                        <input wire:model.live="dateFrom" type="date" id="dateFrom" class="mt-1 block w-full border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm">
                    </div>
                    <div>
                        <label for="dateTo" class="block text-sm font-medium text-gray-700">{{ __('Date To') }}</label>
                        <input wire:model.live="dateTo" type="date" id="dateTo" class="mt-1 block w-full border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm">
                    </div>
                </div>
            </div>

            <!-- Notes Grid -->
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                @forelse($notes as $note)
                    <div wire:key="note-{{ $note->id }}" class="bg-white overflow-hidden shadow-sm sm:rounded-lg border border-gray-200 flex flex-col hover:shadow-md transition-shadow duration-200">
                        <div class="p-6 flex-grow">
                            <h3 class="text-lg font-bold text-gray-900 mb-2 truncate">
                                {{ $note->title ?? __('Untitled Note') }}
                            </h3>
                            <p class="text-sm text-gray-500 mb-4">
                                {{ $note->created_at->translatedFormat('d M Y, H:i') }}
                            </p>
                            <div class="text-gray-700 line-clamp-3 prose prose-sm max-w-none">
                                {{ $note->content }}
                            </div>
                        </div>
                        <div class="bg-gray-50 px-6 py-3 border-t border-gray-100 flex justify-between items-center text-right">
                            <span class="text-xs text-gray-400">ID: {{ $note->id }}</span>
                            <div class="space-x-3 flex items-center">
                                <button wire:click="$dispatch('open-edit-note-modal', { note: {{ $note->id }} })" class="text-indigo-600 hover:text-indigo-900 transition-colors" title="{{ __('Edit') }}">
                                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="size-5">
                                      <path stroke-linecap="round" stroke-linejoin="round" d="m16.862 4.487 1.687-1.688a1.875 1.875 0 1 1 2.652 2.652L10.582 16.07a4.5 4.5 0 0 1-1.897 1.13L6 18l.8-2.685a4.5 4.5 0 0 1 1.13-1.897l8.932-8.931Zm0 0L19.5 7.125M18 14v4.75A2.25 2.25 0 0 1 15.75 21H5.25A2.25 2.25 0 0 1 3 18.75V8.25A2.25 2.25 0 0 1 5.25 6H10" />
                                    </svg>
                                </button>
                                <button wire:click="$dispatch('open-delete-note-modal', { note: {{ $note->id }} })" class="text-red-600 hover:text-red-900 transition-colors" title="{{ __('Delete') }}">
                                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="size-5">
                                      <path stroke-linecap="round" stroke-linejoin="round" d="m14.74 9-.346 9m-4.788 0L9.26 9m9.968-3.21c.342.052.682.107 1.022.166m-1.022-.165L18.16 19.673a2.25 2.25 0 0 1-2.244 2.077H8.084a2.25 2.25 0 0 1-2.244-2.077L4.772 5.79m14.456 0a48.108 48.108 0 0 0-3.478-.397m-12 .562c.34-.059.68-.114 1.022-.165m0 0a48.11 48.11 0 0 1 3.478-.397m7.5 0v-.916c0-1.18-.91-2.164-2.09-2.201a51.964 51.964 0 0 0-3.32 0c-1.18.037-2.09 1.022-2.09 2.201v.916m7.5 0a48.667 48.667 0 0 0-7.5 0" />
                                    </svg>
                                </button>
                            </div>
                        </div>
                    </div>
                @empty
                    <div class="col-span-1 md:col-span-2 lg:col-span-3 bg-white overflow-hidden shadow-sm sm:rounded-lg p-12 text-center">
                        <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" aria-hidden="true">
                            <path vector-effect="non-scaling-stroke" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 13h6m-3-3v6m-9 1V7a2 2 0 012-2h6l2 2h6a2 2 0 012 2v8a2 2 0 01-2 2H5a2 2 0 01-2-2z" />
                        </svg>
                        <h3 class="mt-2 text-sm font-medium text-gray-900">{{ __('No notes found') }}</h3>
                        <p class="mt-1 text-sm text-gray-500">
                            {{ __('Try adjusting your search or date filters.') }}
                        </p>
                    </div>
                @endforelse
            </div>

            <!-- Pagination -->
            <div class="mt-6">
                {{ $notes->links() }}
            </div>

        </div>
    </div>

    <!-- Modals -->
    @livewire('notes.create')
    @livewire('notes.edit')
    @livewire('notes.delete')
</div>
