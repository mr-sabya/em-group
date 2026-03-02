<?php

namespace App\Livewire\Collection;

use App\Models\Collection;
use Livewire\Component;
use Livewire\WithPagination;

class Index extends Component
{
    use WithPagination;

    public $search = '';
    public $perPage = 10;
    public $sortField = 'display_order';
    public $sortDirection = 'asc';

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

    public function deleteCollection($id)
    {
        $collection = Collection::findOrFail($id);
        // Optional: Delete associated image file
        if ($collection->image_path && \Storage::disk('public')->exists($collection->image_path)) {
            \Storage::disk('public')->delete($collection->image_path);
        }
        $collection->delete();

        session()->flash('message', 'Collection deleted successfully.');
        $this->resetPage();
    }

    public function render()
    {
        $collections = Collection::query()
            ->with('category') // Eager load category for display
            ->when($this->search, function ($query) {
                $query->where('title', 'like', '%' . $this->search . '%')
                      ->orWhere('description', 'like', '%' . $this->search . '%')
                      ->orWhere('tag', 'like', '%' . $this->search . '%');
            })
            ->orderBy($this->sortField, $this->sortDirection)
            ->paginate($this->perPage);

        return view('livewire.collection.index', [
            'collections' => $collections,
        ]);
    }
}