<?php

namespace App\Livewire\Admin\Components;

use App\Models\Tenant;
use Livewire\Component;

class TenantSwitcher extends Component
{
    public $tenants;
    public $activeTenant;

    public function mount()
    {
        // Fetch all tenants (or those assigned to the user)
        $this->tenants = Tenant::all();

        // Find the currently active one from session
        $activeId = session('active_tenant_id');
        $this->activeTenant = $this->tenants->where('id', $activeId)->first()
            ?? $this->tenants->first();
    }

    public function switchTenant($id)
    {
        // Update the session
        session(['active_tenant_id' => $id]);

        // Redirect to dashboard or refresh page to apply Global Scopes
        return $this->redirect(route('dashboard.tenant'), navigate: true);
    }

    public function render()
    {
        return view('livewire.admin.components.tenant-switcher');
    }
}
