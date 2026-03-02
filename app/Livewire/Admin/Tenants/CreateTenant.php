<?php

namespace App\Livewire\Admin\Tenants;

use App\Models\Tenant;
use Illuminate\Support\Str;
use Livewire\Component;

class CreateTenant extends Component
{
    public $name = '';
    public $tenant_id = '';

    protected $rules = [
        'name' => 'required|string|min:3|max:100',
        'tenant_id' => 'required|string|alpha_dash|unique:tenants,id|max:50',
    ];

    /**
     * Manually generate the tenant_id from the current name
     */
    public function generateId()
    {
        if (!empty($this->name)) {
            $this->tenant_id = Str::slug($this->name);
        } else {
            $this->addError('name', 'Please enter a store name first.');
        }
    }

    public function save()
    {
        $this->validate();

        // Create the tenant using Stancl Tenancy logic
        $tenant = Tenant::create([
            'id'   => $this->tenant_id,
            'name' => $this->name,
        ]);

        // Set the active session for the admin dashboard context
        session(['active_tenant_id' => $tenant->id]);

        session()->flash('success', 'Store "' . $this->name . '" has been initialized.');

        return redirect()->route('dashboard');
    }

    public function render()
    {
        return view('livewire.admin.tenants.create-tenant');
    }
}
