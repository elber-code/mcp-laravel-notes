<?php

namespace App\Livewire\KeyNotes;

use App\Models\KeyNote;
use Illuminate\Support\Facades\Auth;
use Livewire\Component;
use Livewire\Attributes\On;
use Illuminate\Support\Str;

class Show extends Component
{
    public $isOpen = false;
    public $isFullscreen = false;
    public ?KeyNote $keyNote = null;

    #[On('open-show-key-note-modal')]
    public function openModal(KeyNote $keyNote)
    {
        if ($keyNote->user_id !== Auth::id()) {
            return;
        }
        $this->keyNote = $keyNote;
        $this->isFullscreen = false;
        $this->isOpen = true;
    }

    public function render()
    {
        $markdownContent = '';
        if ($this->keyNote && $this->keyNote->content) {
            $markdownContent = Str::markdown($this->keyNote->content);
        }

        return view('livewire.key-notes.show', [
            'markdownContent' => $markdownContent
        ]);
    }
}
