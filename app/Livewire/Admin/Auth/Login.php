<?php

namespace App\Livewire\Admin\Auth;

use App\Models\Tenant; // Make sure to import the Tenant model
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\ValidationException;
use Livewire\Component;

class Login extends Component
{
    public $email = '';
    public $password = '';
    public $remember = false;

    protected $rules = [
        'email' => 'required|email',
        'password' => 'required',
    ];

    public function authenticate()
    {
        $this->validate();

        if (!Auth::guard('admin')->attempt(['email' => $this->email, 'password' => $this->password], $this->remember)) {
            throw ValidationException::withMessages([
                'email' => __('auth.failed'),
            ]);
        }

        session()->regenerate();

        // --- NEW MULTI-TENANT LOGIC START ---

        // 1. Check if any tenant exists in the system
        $firstTenant = Tenant::orderBy('created_at', 'asc')->first();

        if (!$firstTenant) {
            /**
             * 2. If NO tenants found, redirect to the store creation/onboarding page.
             * Replace 'admin.tenants.create' with your actual route name.
             */
            return redirect()->route('tenants.create');
        }

        // 3. If tenants exist, set the first one as active in the session
        session(['active_tenant_id' => $firstTenant->id]);

        // --- NEW MULTI-TENANT LOGIC END ---

        return redirect()->route('dashboard.index');
    }

    public function render()
    {
        return view('livewire.admin.auth.login');
    }
}
