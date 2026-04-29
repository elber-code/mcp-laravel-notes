<?php

namespace App\Livewire\KeyNotes;

use App\Models\KeyNote;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;
use Livewire\Component;
use Livewire\Attributes\On;

class Edit extends Component
{
    public $isOpen = false;
    public ?KeyNote $note = null;
    public $key = '';
    public $title = '';
    public $content = '';

    #[On('open-edit-keynote-modal')]
    public function openModal(KeyNote $note)
    {
        if ($note->user_id !== Auth::id()) {
            return;
        }
        $this->resetValidation();
        $this->note = $note;
        $this->key = $note->key;
        $this->title = $note->title;
        $this->content = $note->content;
        $this->isOpen = true;
    }

    public function save()
    {
        $this->validate([
            'key' => [
                'required', 
                'string', 
                'max:255', 
                Rule::unique('key_notes')->where(fn ($query) => $query->where('user_id', Auth::id()))->ignore($this->note->id)
            ],
            'title' => 'nullable|string|max:255',
            'content' => 'required|string',
        ]);

        $this->note->update([
            'key' => $this->key,
            'title' => $this->title,
            'content' => $this->content,
        ]);

        $this->isOpen = false;
        $this->dispatch('keynote-updated');
    }

    public function render()
    {
        return view('livewire.key-notes.edit');
    }
}
