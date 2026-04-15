<?php

namespace App\Livewire\Admin\Home;

use App\Models\Order;
use App\Models\User;
use App\Models\Product;
use App\Models\Admin;
use App\Enums\OrderStatus;
use App\Enums\OrderSource;
use Livewire\Component;
use Illuminate\Support\Facades\DB;

class Index extends Component
{
    public function render()
    {
        // 1. KPIs - Global scope handles tenant isolation
        $totalRevenue = Order::where('status', '!=', OrderStatus::Cancelled)->sum('total_amount');
        $totalOrders = Order::count();
        // Count unique phone numbers to see actual customer reach
        $totalCustomers = Order::distinct('phone')->count('phone');
        $activeProducts = Product::where('is_active', true)->count();

        // 2. Sales Chart Data (Last 30 Days)
        $salesData = Order::where('status', '!=', OrderStatus::Cancelled)
            ->where('placed_at', '>=', now()->subDays(30))
            ->select(
                DB::raw('DATE(placed_at) as date'),
                DB::raw('SUM(total_amount) as total')
            )
            ->groupBy('date')
            ->orderBy('date')
            ->get();

        // 3. Newest Orders (Eager load Agent and Courier)
        $recentOrders = Order::with(['admin', 'courier'])
            ->latest()
            ->take(8)
            ->get();

        // 4. Sales by Source (Marketing Analysis)
        $salesBySource = Order::query()
            ->select('source', DB::raw('COUNT(id) as count'), DB::raw('SUM(total_amount) as total'))
            ->groupBy('source')
            ->orderByDesc('total')
            ->get();

        // 5. Top Performing Agents (Leaderboard)
        $topAgents = Admin::withCount(['orders' => fn($q) => $q->where('status', OrderStatus::Delivered)])
            ->withSum(['orders' => fn($q) => $q->where('status', OrderStatus::Delivered)], 'total_amount')
            ->orderByDesc('orders_sum_total_amount')
            ->take(5)
            ->get();

        return view('livewire.admin.home.index', [
            'totalRevenue'   => $totalRevenue,
            'totalOrders'    => $totalOrders,
            'totalCustomers' => $totalCustomers,
            'activeProducts' => $activeProducts,
            'recentOrders'   => $recentOrders,
            'salesBySource'  => $salesBySource,
            'topAgents'      => $topAgents,
            'chartLabels'    => $salesData->pluck('date'),
            'chartValues'    => $salesData->pluck('total'),
        ]);
    }
}
