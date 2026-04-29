<?php

namespace App\Livewire\KeyNotes;

use App\Models\KeyNote;
use Illuminate\Support\Facades\Auth;
use Livewire\Component;
use Livewire\Attributes\On;

class Delete extends Component
{
    public $isOpen = false;
    public ?KeyNote $note = null;

    #[On('open-delete-keynote-modal')]
    public function openModal(KeyNote $note)
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
        $this->dispatch('keynote-deleted');
    }

    public function render()
    {
        return view('livewire.key-notes.delete');
    }
}
