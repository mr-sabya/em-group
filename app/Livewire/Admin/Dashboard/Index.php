<?php

namespace App\Livewire\Admin\Dashboard;

use App\Models\Admin;
use App\Models\Order;
use App\Models\Tenant;
use App\Models\User;
use Livewire\Component;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Spatie\Permission\Models\Role;

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
        $tenant = Tenant::find($tenantId);

        if ($tenant) {
            session(['active_tenant_id' => $tenant->id]);
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

        // 2. Executive Admins Logic
        // We first check if the role record even exists to prevent Spatie "RoleDoesNotExist" exception
        $roleExists = Role::where('name', 'executive')->where('guard_name', 'admin')->exists();

        $executives = 0; // Default to 0

        if ($roleExists) {
            $executivesCollection = Admin::role('executive', 'admin')
                ->with(['tenants'])
                ->withCount(['orders' => function ($q) {
                    $q->withoutGlobalScopes();
                }])
                ->get();

            // If query returns results, use the collection, otherwise keep it as 0
            if ($executivesCollection->isNotEmpty()) {
                $executives = $executivesCollection;
            }
        }

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
