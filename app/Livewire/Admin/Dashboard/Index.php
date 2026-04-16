<?php

namespace App\Livewire\Admin\Dashboard;

use App\Models\Admin;
use App\Models\Order;
use App\Models\Tenant;
use App\Models\User;
use Livewire\Component;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class Index extends Component
{
    public $showTenantModal = false;
    public $tenantName, $tenantId;

    public function createTenant()
    {
        $this->validate([
            'tenantName' => 'required|string|max:255',
            'tenantId'   => 'required|string|unique:tenants,id|alpha_dash',
        ]);

        $tenant = Tenant::create([
            'id'   => Str::slug($this->tenantId),
            'name' => $this->tenantName,
        ]);

        $tenant->domains()->create([
            'domain' => $this->tenantId . '.' . config('tenancy.central_domain', 'localhost')
        ]);

        $this->reset(['tenantName', 'tenantId', 'showTenantModal']);
        session()->flash('success', 'New Tenant (Store) Provisioned Successfully!');
    }

    /**
     * Switch the session to a specific tenant and redirect to the dashboard.
     */
    public function switchTenant($tenantId)
    {
        // 1. Validate the tenant exists
        $tenant = \App\Models\Tenant::find($tenantId);

        if ($tenant) {
            // 2. Set the session variable that your Global Scopes and Sidebar look for
            session(['active_tenant_id' => $tenant->id]);

            // 3. Redirect to the dashboard (now filtered by this tenant)
            // 'wire:navigate' compatible redirect
            return $this->redirectRoute('dashboard.tenant', navigate: true);
        }

        session()->flash('error', 'Tenant not found.');
    }

    public function render()
    {
        // 1. Global Aggregates (Bypassing Scopes)
        $stats = [
            'total_revenue' => Order::withoutGlobalScopes()->sum('total_amount'),
            'total_orders'  => Order::withoutGlobalScopes()->count(),
            'total_tenants' => Tenant::count(),
            'total_admins'  => Admin::count(),
            'total_users'   => User::count(),
        ];

        // 2. Executive Admins with Assigned Tenants
        // We filter admins who have the 'executive' role
        $executives = Admin::role('executive', 'admin')
            ->with(['tenants'])
            ->withCount(['orders' => function ($q) {
                $q->withoutGlobalScopes();
            }])
            ->get();

        // 3. Tenant List with Performance
        $tenants = Tenant::with(['domains'])->get()->map(function ($t) {
            $t->performance = Order::withoutGlobalScopes()->where('tenant_id', $t->id)->sum('total_amount');
            $t->order_count = Order::withoutGlobalScopes()->where('tenant_id', $t->id)->count();
            return $t;
        })->sortByDesc('performance');

        // 4. Chart Data: Last 7 Days Revenue
        $days = collect(range(6, 0))->map(fn($i) => now()->subDays($i)->format('Y-m-d'));
        $chartData = Order::withoutGlobalScopes()
            ->select(DB::raw('DATE(created_at) as date'), DB::raw('SUM(total_amount) as total'))
            ->where('created_at', '>=', now()->subDays(7))
            ->groupBy('date')
            ->get()
            ->pluck('total', 'date');

        $chartValues = $days->map(fn($date) => $chartData[$date] ?? 0);
        $chartLabels = $days->map(fn($date) => date('D', strtotime($date)));

        return view('livewire.admin.dashboard.index', [
            'stats'       => $stats,
            'executives'  => $executives,
            'tenants'     => $tenants,
            'chartLabels' => $chartLabels,
            'chartValues' => $chartValues,
        ]);
    }
}
