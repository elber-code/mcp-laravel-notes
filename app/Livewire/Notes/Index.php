<?php

namespace App\Livewire\Notes;

use App\Models\Note;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;
use Livewire\Component;
use Livewire\WithPagination;
use Livewire\Attributes\On;

class Index extends Component
{
    use WithPagination;

    public $search = '';
    public $dateFrom = '';
    public $dateTo = '';
    public $selectedFilterTags = [];

    // Actualiza la URL para que los filtros se mantengan al recargar
    protected $queryString = [
        'search' => ['except' => ''],
        'dateFrom' => ['except' => ''],
        'dateTo' => ['except' => ''],
        'selectedFilterTags' => ['except' => []],
    ];

    public function updatingSearch()
    {
        $this->resetPage();
    }

    public function updatingDateFrom()
    {
        $this->resetPage();
    }

    public function updatingDateTo()
    {
        $this->resetPage();
    }

    public function toggleTagFilter($tag)
    {
        if (in_array($tag, $this->selectedFilterTags)) {
            $this->selectedFilterTags = array_values(array_diff($this->selectedFilterTags, [$tag]));
        } else {
            $this->selectedFilterTags[] = $tag;
        }
        $this->resetPage();
    }

    #[On('note-created')]
    #[On('note-updated')]
    #[On('note-deleted')]
    public function refreshNotes()
    {
        // El componente se re-renderizará automáticamente
    }

    public function render()
    {
        $query = Note::where('user_id', Auth::id());

        if (!empty($this->search)) {
            $query->where(function ($q) {
                $q->where('title', 'like', '%' . $this->search . '%')
                  ->orWhere('content', 'like', '%' . $this->search . '%');
            });
        }

        if (!empty($this->dateFrom)) {
            $query->whereDate('created_at', '>=', Carbon::parse($this->dateFrom));
        }

        if (!empty($this->dateTo)) {
            $query->whereDate('created_at', '<=', Carbon::parse($this->dateTo));
        }

        if (!empty($this->selectedFilterTags)) {
            $query->where(function ($q) {
                foreach ($this->selectedFilterTags as $tag) {
                    $q->orWhereJsonContains('tags', $tag);
                }
            });
        }

        $notes = $query->latest()->paginate(9);
        $allTags = \App\Models\Tag::where('user_id', Auth::id())->orderBy('name')->pluck('name')->toArray();

        return view('livewire.notes.index', [
            'notes' => $notes,
            'allTags' => $allTags
        ])->layout('layouts.app'); // Jetstream's app layout
    }
}
