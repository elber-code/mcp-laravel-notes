<?php

namespace App\Livewire\Notes;

use App\Models\Note;
use Illuminate\Support\Facades\Auth;
use Livewire\Component;
use Livewire\Attributes\On;

class Create extends Component
{
    public $isOpen = false;
    public $title = '';
    public $content = '';

    #[On('open-create-note-modal')]
    public function openModal()
    {
        $this->reset(['title', 'content']);
        $this->resetValidation();
        $this->isOpen = true;
    }

    public function save()
    {
        $this->validate([
            'title' => 'nullable|string|max:255',
            'content' => 'required|string',
        ]);

        Note::create([
            'user_id' => Auth::id(),
            'title' => $this->title,
            'content' => $this->content,
        ]);

        $this->isOpen = false;
        $this->dispatch('note-created');
    }

    public function render()
    {
        return view('livewire.notes.create');
    }
}
