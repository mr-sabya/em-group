<?php

namespace App\Livewire\Admin\Deal;

use App\Models\Deal;
use App\Models\Tenant;
use Livewire\Component;
use Livewire\WithPagination;
use Livewire\Attributes\Title;
use Livewire\Attributes\Computed; // Added

class Index extends Component
{
    use WithPagination;

    public $search = '';
    public $perPage = 10;
    public $sortField = 'starts_at';
    public $sortDirection = 'desc';

    protected $queryString = ['search', 'perPage', 'sortField', 'sortDirection'];

    /**
     * Computed property to get the active store/tenant info.
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

    public function deleteDeal($id)
    {
        // Replaced Deal::destroy with find() to ensure the global tenant scope 
        // validates that the user owns this record before deletion.
        $deal = Deal::find($id);

        if ($deal) {
            $deal->delete();
            session()->flash('message', 'Deal deleted successfully.');
        } else {
            session()->flash('error', 'Deal not found or access denied.');
        }

        $this->resetPage();
    }

    public function render()
    {
        $deals = Deal::query()
            ->when($this->search, function ($query) {
                /** 
                 * Grouped search closure is CRITICAL for multi-tenancy.
                 * Prevents OR logic from bypassing the tenant_id scope.
                 */
                $query->where(function ($q) {
                    $q->where('name', 'like', '%' . $this->search . '%')
                        ->orWhere('description', 'like', '%' . $this->search . '%');
                });
            })
            ->orderBy($this->sortField, $this->sortDirection)
            ->paginate($this->perPage);

        return view('livewire.admin.deal.index', [
            'deals' => $deals,
        ]);
    }
}
