<?php

namespace App\Livewire\Notes;

use App\Models\Note;
use Illuminate\Support\Facades\Auth;
use Livewire\Component;
use Livewire\Attributes\On;
use Illuminate\Support\Str;

class Show extends Component
{
    public $isOpen = false;
    public $isFullscreen = false;
    public ?Note $note = null;

    #[On('open-show-note-modal')]
    public function openModal(Note $note)
    {
        if ($note->user_id !== Auth::id()) {
            return;
        }
        $this->note = $note;
        $this->isFullscreen = false;
        $this->isOpen = true;
    }

    public function render()
    {
        $markdownContent = '';
        if ($this->note && $this->note->content) {
            $markdownContent = Str::markdown($this->note->content);
        }

        return view('livewire.notes.show', [
            'markdownContent' => $markdownContent
        ]);
    }
}
