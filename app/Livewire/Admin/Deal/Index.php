<?php

namespace App\Livewire\Deal;

use App\Models\Deal;
use Livewire\Component;
use Livewire\WithPagination;
use Livewire\Attributes\Title; // Livewire 3 title attribute

class Index extends Component
{
    use WithPagination;

    public $search = '';
    public $perPage = 10;
    public $sortField = 'starts_at';
    public $sortDirection = 'desc';

    protected $queryString = ['search', 'perPage', 'sortField', 'sortDirection'];

    public function updatingSearch()
    {
        $this->resetPage();
    }

    public function sortBy($field)
    {
        if ($this->sortField === $field) {
            $this->sortDirection = $this->sortDirection === 'asc' ? 'desc' : 'asc';
        } else {
            $this->sortDirection = 'asc';
        }
        $this->sortField = $field;
    }

    public function deleteDeal($id)
    {
        Deal::destroy($id);
        session()->flash('message', 'Deal deleted successfully.');
        $this->resetPage();
    }

    public function render()
    {
        $deals = Deal::query()
            ->when($this->search, function ($query) {
                $query->where('name', 'like', '%' . $this->search . '%')
                    ->orWhere('description', 'like', '%' . $this->search . '%');
            })
            ->orderBy($this->sortField, $this->sortDirection)
            ->paginate($this->perPage);

        return view('livewire.deal.index', [
            'deals' => $deals,
        ]);
    }
}
