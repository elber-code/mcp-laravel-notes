<?php

namespace App\Livewire\KeyNotes;

use App\Models\KeyNote;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;
use Livewire\Component;
use Livewire\Attributes\On;

use App\Livewire\Traits\HasTags;

class Create extends Component
{
    use HasTags;

    public $isOpen = false;
    public $key = '';
    public $title = '';
    public $content = '';

    #[On('open-create-keynote-modal')]
    public function openModal()
    {
        $this->reset(['key', 'title', 'content', 'selectedTags', 'tagSearch', 'isAddingTag']);
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

        $this->syncTagsToDatabase();

        KeyNote::create([
            'user_id' => Auth::id(),
            'key' => $this->key,
            'title' => $this->title,
            'content' => $this->content,
            'tags' => empty($this->selectedTags) ? null : $this->selectedTags,
        ]);

        $this->isOpen = false;
        $this->dispatch('keynote-created');
    }

    public function render()
    {
        return view('livewire.key-notes.create', [
            'availableTags' => $this->getAvailableTags()
        ]);
    }
}
