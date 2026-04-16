<?php

namespace App\Livewire\Admin\Theme;

use Livewire\Attributes\Computed; // Added for Computed Property
use Livewire\Component;

class Sidebar extends Component
{

    /**
     * BEST WAY: Computed Property to get the current tenant.
     * Accessible in Class/Blade via $this->currentTenant
     */
    #[Computed]
    public function currentTenant()
    {
        return \App\Models\Tenant::find(session('active_tenant_id'));
    }


    public function render()
    {
        return view('livewire.admin.theme.sidebar');
    }
}
