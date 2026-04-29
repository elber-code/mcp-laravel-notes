<?php

namespace App\Livewire\KeyNotes;

use App\Models\KeyNote;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;
use Livewire\Component;
use Livewire\Attributes\On;

class Create extends Component
{
    public $isOpen = false;
    public $key = '';
    public $title = '';
    public $content = '';

    #[On('open-create-keynote-modal')]
    public function openModal()
    {
        $this->reset(['key', 'title', 'content']);
        $this->resetValidation();
        $this->isOpen = true;
    }

    public function save()
    {
        $this->validate([
            'key' => [
                'required', 
                'string', 
                'max:255', 
                Rule::unique('key_notes')->where(fn ($query) => $query->where('user_id', Auth::id()))
            ],
            'title' => 'nullable|string|max:255',
            'content' => 'required|string',
        ]);

        KeyNote::create([
            'user_id' => Auth::id(),
            'key' => $this->key,
            'title' => $this->title,
            'content' => $this->content,
        ]);

        $this->isOpen = false;
        $this->dispatch('keynote-created');
    }

    public function render()
    {
        return view('livewire.key-notes.create');
    }
}
