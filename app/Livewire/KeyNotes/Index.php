<?php

namespace App\Livewire\KeyNotes;

use App\Models\KeyNote;
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

    protected $queryString = [
        'search' => ['except' => ''],
        'dateFrom' => ['except' => ''],
        'dateTo' => ['except' => ''],
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

    #[On('keynote-created')]
    #[On('keynote-updated')]
    #[On('keynote-deleted')]
    public function refreshNotes()
    {
        // El componente se re-renderizará automáticamente
    }

    public function render()
    {
        $query = KeyNote::where('user_id', Auth::id());

        if (!empty($this->search)) {
            $query->where(function ($q) {
                $q->where('key', 'like', '%' . $this->search . '%')
                  ->orWhere('title', 'like', '%' . $this->search . '%')
                  ->orWhere('content', 'like', '%' . $this->search . '%');
            });
        }

        if (!empty($this->dateFrom)) {
            $query->whereDate('created_at', '>=', Carbon::parse($this->dateFrom));
        }

        if (!empty($this->dateTo)) {
            $query->whereDate('created_at', '<=', Carbon::parse($this->dateTo));
        }

        $notes = $query->latest()->paginate(9);

        return view('livewire.key-notes.index', [
            'notes' => $notes
        ])->layout('layouts.app');
    }
}
