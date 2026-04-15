<?php

namespace App\Livewire\Admin\Couriers;

use App\Models\Courier;
use App\Models\Tenant;
use Livewire\Component;
use Livewire\WithPagination;
use Livewire\Attributes\Computed;
use Illuminate\Support\Facades\DB;

class CourierManager extends Component
{
    use WithPagination;

    protected $paginationTheme = 'bootstrap';

    // --- Table Properties ---
    public $search = '';
    public $sortField = 'name';
    public $sortDirection = 'asc';

    // --- Form Properties ---
    public $courierId;
    public $name, $vendor = 'custom', $is_active = true;
    public $credentials = [];
    public $isEditing = false;

    /**
     * Computed property to get the current tenant info
     */
    #[Computed]
    public function currentTenant()
    {
        return Tenant::find(session('active_tenant_id'));
    }

    public function updatingSearch() { $this->resetPage(); }

    public function updatedVendor()
    {
        $this->credentials = [];
        $this->resetErrorBag();
    }

    public function resetInputs()
    {
        $this->reset(['courierId', 'name', 'vendor', 'is_active', 'isEditing', 'credentials']);
        $this->resetErrorBag();
    }

    public function openCreateModal()
    {
        $this->resetInputs();
        $this->isEditing = false;
        $this->dispatch('show-courier-modal');
    }

    public function edit($id)
    {
        $this->resetInputs();
        $this->isEditing = true;
        // Scope query to current tenant session
        $courier = Courier::where('tenant_id', session('active_tenant_id'))->findOrFail($id);
        
        $this->courierId = $courier->id;
        $this->name = $courier->name;
        $this->vendor = $courier->vendor;
        $this->is_active = $courier->is_active;
        $this->credentials = $courier->credentials ?? [];

        $this->dispatch('show-courier-modal');
    }

    protected function getValidationRules()
    {
        $rules = [
            'name' => 'required|string|max:255',
            'vendor' => 'required',
        ];

        if ($this->vendor === 'pathao') {
            $rules = array_merge($rules, [
                'credentials.client_id' => 'required',
                'credentials.client_secret' => 'required',
                'credentials.username' => 'required',
                'credentials.password' => 'required',
                'credentials.grant_type' => 'required',
                'credentials.store_id' => 'required',
            ]);
        } elseif ($this->vendor === 'steadfast') {
            $rules = array_merge($rules, [
                'credentials.api_key' => 'required',
                'credentials.secret_key' => 'required',
            ]);
        } elseif ($this->vendor === 'redx') {
            $rules['credentials.api_token'] = 'required';
        } elseif ($this->vendor === 'carrybee') {
            $rules = array_merge($rules, [
                'credentials.client_id' => 'required',
                'credentials.client_secret' => 'required',
                'credentials.client_context' => 'required',
                'credentials.store_id' => 'required',
                'credentials.delivery_type' => 'required',
            ]);
        }

        return $rules;
    }

    public function save()
    {
        $this->validate($this->getValidationRules());

        $data = [
            'tenant_id' => session('active_tenant_id'), // Use current session tenant
            'name' => $this->name,
            'vendor' => $this->vendor,
            'is_active' => $this->is_active,
            'credentials' => $this->credentials,
        ];

        Courier::updateOrCreate(['id' => $this->courierId], $data);

        $this->dispatch('hide-courier-modal');
        session()->flash('message', $this->isEditing ? 'Courier Updated' : 'Courier Created');
        $this->resetInputs();
    }

    public function render()
    {
        $couriers = Courier::query()
            ->where('tenant_id', session('active_tenant_id'))
            ->when($this->search, function($query) {
                $query->where(function ($q) {
                    $q->where('name', 'like', "%{$this->search}%")
                      ->orWhere('vendor', 'like', "%{$this->search}%");
                });
            })
            ->orderBy($this->sortField, $this->sortDirection)
            ->paginate(10);

        return view('livewire.admin.couriers.courier-manager', ['couriers' => $couriers]);
    }
}