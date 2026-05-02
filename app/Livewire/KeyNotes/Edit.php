<?php

namespace App\Livewire\KeyNotes;

use App\Models\KeyNote;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;
use Livewire\Component;
use Livewire\Attributes\On;

use App\Livewire\Traits\HasTags;

class Edit extends Component
{
    use HasTags;

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
        $this->selectedTags = is_array($note->tags) ? $note->tags : [];
        $this->tagSearch = '';
        $this->isAddingTag = false;
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

        $this->syncTagsToDatabase();

        $this->note->update([
            'key' => $this->key,
            'title' => $this->title,
            'content' => $this->content,
            'tags' => empty($this->selectedTags) ? null : $this->selectedTags,
        ]);

        $this->isOpen = false;
        $this->dispatch('keynote-updated');
    }

    public function render()
    {
        return view('livewire.key-notes.edit', [
            'availableTags' => $this->getAvailableTags()
        ]);
    }
}
