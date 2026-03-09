<?php

namespace App\Livewire\Admin\Collection;

use App\Models\Collection;
use App\Models\Tenant;
use Illuminate\Support\Facades\Storage;
use Livewire\Component;
use Livewire\WithPagination;
use Livewire\Attributes\Computed; // Added for Computed Property

class Index extends Component
{
    use WithPagination;

    public $search = '';
    public $perPage = 10;
    public $sortField = 'display_order';
    public $sortDirection = 'asc';

    protected $queryString = ['search', 'perPage', 'sortField', 'sortDirection'];

    /**
     * Computed property to access the active tenant info.
     */
    #[Computed]
    public function currentTenant()
    {
        return Tenant::find(session('active_tenant_id'));
    }

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
        // Global scope ensures we only find collections belonging to the active tenant
        $collection = Collection::find($id);

        if (!$collection) {
            session()->flash('error', 'Collection not found or access denied.');
            return;
        }

        if ($collection->image_path && Storage::disk('public')->exists($collection->image_path)) {
            Storage::disk('public')->delete($collection->image_path);
        }

        $collection->delete();

        session()->flash('message', 'Collection deleted successfully.');
        $this->resetPage();
    }

    public function render()
    {
        $collections = Collection::query()
            ->with('category')
            ->when($this->search, function ($query) {
                /** 
                 * Grouped OR conditions are CRITICAL for multi-tenancy.
                 * This prevents the search from bypassing the Global Tenant Scope.
                 */
                $query->where(function ($q) {
                    $q->where('title', 'like', '%' . $this->search . '%')
                        ->orWhere('description', 'like', '%' . $this->search . '%')
                        ->orWhere('tag', 'like', '%' . $this->search . '%');
                });
            })
            ->orderBy($this->sortField, $this->sortDirection)
            ->paginate($this->perPage);

        return view('livewire.admin.collection.index', [
            'collections' => $collections,
        ]);
    }
}
