<?php

namespace App\Livewire\Admin\CancelReasons;

use App\Models\CancelReason;
use App\Models\Tenant;
use Livewire\Component;
use Livewire\WithPagination;
use Livewire\Attributes\Computed;
use Illuminate\Validation\Rule;

class Manage extends Component
{
    use WithPagination;

    protected $paginationTheme = 'bootstrap';

    public $search = '';
    public $sortField = 'name';
    public $sortDirection = 'asc';

    public $reasonId;
    public $name, $color = '#e61e1e', $isEditing = false;

    #[Computed]
    public function currentTenant()
    {
        return Tenant::find(session('active_tenant_id'));
    }

    public function updatingSearch() { $this->resetPage(); }

    public function resetInputs()
    {
        $this->reset(['reasonId', 'name', 'isEditing']);
        $this->color = '#e61e1e';
        $this->resetErrorBag();
    }

    public function openCreateModal()
    {
        $this->resetInputs();
        $this->isEditing = false;
        $this->dispatch('show-reason-modal');
    }

    public function edit($id)
    {
        $this->resetInputs();
        $this->isEditing = true;
        // Tenant scope check
        $reason = CancelReason::where('tenant_id', session('active_tenant_id'))->findOrFail($id);
        
        $this->reasonId = $reason->id;
        $this->name = $reason->name;
        $this->color = $reason->color;

        $this->dispatch('show-reason-modal');
    }

    public function save()
    {
        $this->validate([
            'name' => [
                'required', 'string', 'max:255',
                // Unique per tenant check
                Rule::unique('cancel_reasons', 'name')
                    ->where('tenant_id', session('active_tenant_id'))
                    ->ignore($this->reasonId)
            ],
            'color' => 'nullable|string|max:20',
        ]);

        CancelReason::updateOrCreate(['id' => $this->reasonId], [
            'tenant_id' => session('active_tenant_id'),
            'name' => $this->name,
            'color' => $this->color,
        ]);

        $this->dispatch('hide-reason-modal');
        session()->flash('message', $this->isEditing ? 'Reason Updated' : 'Reason Created');
        $this->resetInputs();
    }

    public function render()
    {
        $reasons = CancelReason::query()
            ->where('tenant_id', session('active_tenant_id'))
            ->when($this->search, function($q) {
                $q->where('name', 'like', "%{$this->search}%");
            })
            ->orderBy($this->sortField, $this->sortDirection)
            ->paginate(10);

        return view('livewire.admin.cancel-reasons.manage', ['reasons' => $reasons]);
    }
}