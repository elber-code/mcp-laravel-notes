<?php

namespace App\Livewire\Traits;

use App\Models\Tag;
use Illuminate\Support\Facades\Auth;

trait HasTags
{
    public $selectedTags = [];
    public $tagSearch = '';
    public $isAddingTag = false;

    public function toggleAddTag()
    {
        $this->isAddingTag = !$this->isAddingTag;
        $this->tagSearch = '';
    }

    public function addTag($tagName)
    {
        $tagName = trim(strtolower($tagName));
        if (!empty($tagName) && !in_array($tagName, $this->selectedTags)) {
            $this->selectedTags[] = $tagName;
        }
        $this->tagSearch = '';
        $this->isAddingTag = false;
    }

    public function removeTag($tagName)
    {
        $this->selectedTags = array_values(array_filter($this->selectedTags, fn($t) => $t !== $tagName));
    }

    public function syncTagsToDatabase()
    {
        foreach ($this->selectedTags as $tagName) {
            Tag::firstOrCreate([
                'user_id' => Auth::id(),
                'name' => $tagName
            ]);
        }
    }

    public function getAvailableTags()
    {
        if (!$this->isAddingTag) {
            return [];
        }

        $query = Tag::where('user_id', Auth::id())
            ->whereNotIn('name', $this->selectedTags);
            
        if (!empty($this->tagSearch)) {
            $query->where('name', 'like', '%' . $this->tagSearch . '%');
        }
        
        return $query->take(5)->pluck('name')->toArray();
    }
}
