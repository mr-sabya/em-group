<?php

namespace App\Livewire\Admin\Home;

use App\Models\Order;
use App\Models\User;
use App\Models\Product;
use App\Enums\UserRole;
use App\Enums\OrderStatus;
use Livewire\Component;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class Index extends Component
{
    public function render()
    {
        // 1. Key Performance Indicators (KPIs)
        $totalRevenue = Order::where('order_status', '!=', OrderStatus::Cancelled)->sum('total_amount');
        $totalOrders = Order::count();
        $totalCustomers = User::count();
        $activeProducts = Product::active()->count();

        // 2. Sales Chart Data (Last 30 Days)
        $salesData = Order::where('order_status', '!=', OrderStatus::Cancelled)
            ->where('placed_at', '>=', now()->subDays(30))
            ->select(
                DB::raw('DATE(placed_at) as date'),
                DB::raw('SUM(total_amount) as total'),
                DB::raw('COUNT(*) as count')
            )
            ->groupBy('date')
            ->get();

        // 3. New Customers Table (Detailed)
        $newCustomers = User::withCount('orders')
            ->withSum(['orders' => fn($q) => $q->where('order_status', '!=', OrderStatus::Cancelled)], 'total_amount')
            ->latest()
            ->take(5)
            ->get();

        // 4. Recent Orders (WooCommerce Style)
        $recentOrders = Order::with(['user', 'shippingCountry'])
            ->latest()
            ->take(8)
            ->get();

        // 5. Sales by Country
        $salesByCountry = Order::query()
            ->join('countries', 'orders.shipping_country_id', '=', 'countries.id')
            ->select(
                'countries.name',
                DB::raw('COUNT(orders.id) as order_count'),
                DB::raw('SUM(total_amount) as total_sales')
            )
            ->groupBy('countries.name')
            ->orderByDesc('total_sales')
            ->take(5)
            ->get();

        return view('livewire.admin.home.index', [
            'totalRevenue' => $totalRevenue,
            'totalOrders' => $totalOrders,
            'totalCustomers' => $totalCustomers,
            'activeProducts' => $activeProducts,
            'newCustomers' => $newCustomers,
            'recentOrders' => $recentOrders,
            'salesByCountry' => $salesByCountry,
            'chartLabels' => $salesData->pluck('date'),
            'chartValues' => $salesData->pluck('total'),
        ]);
    }
}
