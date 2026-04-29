<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Dashboard') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                <!-- Timeline Notes Card -->
                <div class="bg-white overflow-hidden shadow-xl sm:rounded-lg p-6 flex flex-col justify-between">
                    <div>
                        <h3 class="text-2xl font-bold text-gray-800 mb-2">{{ __('Notes') }}</h3>
                        <p class="text-gray-600 mb-4">
                            {{ __('Normal notes sorted by creation date. Great for journaling or sequential logs.') }}
                        </p>
                        <p class="text-4xl font-extrabold text-indigo-600 mb-6">
                            {{ auth()->user()->notes()->count() }}
                        </p>
                    </div>
                    <a href="{{ route('notes.index') }}"
                        class="inline-flex items-center justify-center px-4 py-2 bg-indigo-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-indigo-700 focus:bg-indigo-700 active:bg-indigo-900 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 transition ease-in-out duration-150 w-full text-center">
                        {{ __('View Notes') }}
                    </a>
                </div>

                <!-- Key Notes Card -->
                <div class="bg-white overflow-hidden shadow-xl sm:rounded-lg p-6 flex flex-col justify-between">
                    <div>
                        <h3 class="text-2xl font-bold text-gray-800 mb-2">{{ __('Key Notes') }}</h3>
                        <p class="text-gray-600 mb-4">
                            {{ __('Specialized notes identified by a unique key. Ideal for settings, memory, and references.') }}
                        </p>
                        <p class="text-4xl font-extrabold text-emerald-600 mb-6">
                            {{ auth()->user()->keyNotes()->count() }}
                        </p>
                    </div>
                    <a href="{{ route('key-notes.index') }}"
                        class="inline-flex items-center justify-center px-4 py-2 bg-emerald-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-emerald-700 focus:bg-emerald-700 active:bg-emerald-900 focus:outline-none focus:ring-2 focus:ring-emerald-500 focus:ring-offset-2 transition ease-in-out duration-150 w-full text-center">
                        {{ __('View Key Notes') }}
                    </a>
                </div>

                <!-- API Tokens Card -->
                <div class="bg-white overflow-hidden shadow-xl sm:rounded-lg p-6 flex flex-col justify-between">
                    <div>
                        <h3 class="text-2xl font-bold text-gray-800 mb-2">{{ __('API Tokens') }}</h3>
                        <p class="text-gray-600 mb-4">
                            {{ __('Manage your API tokens to allow AI agents and other applications to securely access your notes.') }}
                        </p>
                        <div class="mb-6 flex items-center justify-start text-indigo-500">
                            <svg class="h-10 w-10 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 7a2 2 0 012 2m4 0a6 6 0 01-7.743 5.743L11 17H9v2H7v2H4a1 1 0 01-1-1v-2.586a1 1 0 01.293-.707l5.964-5.964A6 6 0 1121 9z" />
                            </svg>
                        </div>
                    </div>
                    <a href="{{ route('api-tokens.index') }}"
                        class="inline-flex items-center justify-center px-4 py-2 bg-gray-800 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-gray-700 focus:bg-gray-700 active:bg-gray-900 focus:outline-none focus:ring-2 focus:ring-gray-500 focus:ring-offset-2 transition ease-in-out duration-150 w-full text-center">
                        {{ __('Manage API Tokens') }}
                    </a>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>