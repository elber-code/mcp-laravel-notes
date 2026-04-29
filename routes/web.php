<?php

use App\Livewire\KeyNotes\Index as KeyNotesIndex;
use App\Livewire\Notes\Index as NotesIndex;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});

Route::middleware([
    'auth:sanctum',
    config('jetstream.auth_session'),
    'verified',
])->group(function () {
    Route::get('/dashboard', function () {
        return view('dashboard');
    })->name('dashboard');

    Route::get('/notes', NotesIndex::class)->name('notes.index');
    Route::get('/key-notes', KeyNotesIndex::class)->name('key-notes.index');
});
