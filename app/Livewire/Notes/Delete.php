<?php

namespace App\Livewire\Notes;

use App\Models\Note;
use Illuminate\Support\Facades\Auth;
use Livewire\Component;
use Livewire\Attributes\On;

class Delete extends Component
{
    public $isOpen = false;
    public ?Note $note = null;

    #[On('open-delete-note-modal')]
    public function openModal(Note $note)
    {
        if ($note->user_id !== Auth::id()) {
            return;
        }
        $this->note = $note;
        $this->isOpen = true;
    }

    public function delete()
    {
        if ($this->note) {
            $this->note->delete();
        }
        $this->isOpen = false;
        $this->dispatch('note-deleted');
    }

    public function render()
    {
        return view('livewire.notes.delete');
    }
}
