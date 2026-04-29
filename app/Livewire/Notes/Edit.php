<?php

namespace App\Livewire\Notes;

use App\Models\Note;
use Illuminate\Support\Facades\Auth;
use Livewire\Component;
use Livewire\Attributes\On;

class Edit extends Component
{
    public $isOpen = false;
    public ?Note $note = null;
    public $title = '';
    public $content = '';

    #[On('open-edit-note-modal')]
    public function openModal(Note $note)
    {
        if ($note->user_id !== Auth::id()) {
            return;
        }
        $this->resetValidation();
        $this->note = $note;
        $this->title = $note->title;
        $this->content = $note->content;
        $this->isOpen = true;
    }

    public function save()
    {
        $this->validate([
            'title' => 'nullable|string|max:255',
            'content' => 'required|string',
        ]);

        $this->note->update([
            'title' => $this->title,
            'content' => $this->content,
        ]);

        $this->isOpen = false;
        $this->dispatch('note-updated');
    }

    public function render()
    {
        return view('livewire.notes.edit');
    }
}
