<?php

namespace App\Livewire\Admin\Orders;

use App\Enums\OrderStatus;
use App\Enums\OrderSource;
use App\Models\Order;
use App\Models\Tenant;
use Livewire\Component;
use Livewire\WithPagination;
use Livewire\Attributes\Computed;
use Illuminate\Support\Facades\Response;

class Index extends Component
{
    use WithPagination;

    public $search = '';
    public $perPage = 50;
    public $sortField = 'placed_at';
    public $sortDirection = 'desc';

    public $activeTab = 'all';
    public $selectedOrders = [];
    public $selectAll = false;

    // Modals
    public $showStatusUpdateModal = false;
    public $updateOrderId;
    public $newOrderStatus;

    protected $queryString = [
        'search' => ['except' => ''],
        'perPage' => ['except' => 50],
        'activeTab' => ['except' => 'all']
    ];

    #[Computed]
    public function currentTenant()
    {
        return Tenant::find(session('active_tenant_id'));
    }

    public function updatedSelectAll($value)
    {
        if ($value) {
            $this->selectedOrders = $this->getOrderQuery()
                ->pluck('id')
                ->map(fn($id) => (string) $id)
                ->toArray();
        } else {
            $this->selectedOrders = [];
        }
    }

    public function setTab($tab)
    {
        $this->activeTab = $tab;
        $this->selectedOrders = [];
        $this->selectAll = false;
        $this->resetPage();
    }

    public function openStatusUpdateModal($orderId)
    {
        $order = Order::findOrFail($orderId);
        $this->updateOrderId = $orderId;
        $this->newOrderStatus = $order->status->value;
        $this->showStatusUpdateModal = true;
    }

    public function updateOrderStatus()
    {
        $order = Order::findOrFail($this->updateOrderId);
        $order->update(['status' => $this->newOrderStatus]);

        $this->dispatch('close-modal-now');
        $this->showStatusUpdateModal = false;
        session()->flash('message', 'Order status updated to ' . $this->newOrderStatus);
    }

    public function bulkConfirm()
    {
        if (empty($this->selectedOrders)) return;
        Order::whereIn('id', $this->selectedOrders)->update(['status' => OrderStatus::Confirmed->value]);
        $this->selectedOrders = [];
        $this->selectAll = false;
    }

    public function exportCSV()
    {
        if (empty($this->selectedOrders)) return;

        return Response::streamDownload(function () {
            $handle = fopen('php://output', 'w');
            fputcsv($handle, ['Order No', 'Date', 'Customer', 'Phone', 'Amount', 'Status', 'Source']);

            Order::whereIn('id', $this->selectedOrders)->each(function ($o) use ($handle) {
                fputcsv($handle, [
                    $o->order_number,
                    $o->placed_at->format('Y-m-d'),
                    $o->name,
                    $o->phone,
                    $o->total_amount,
                    $o->status->value,
                    $o->source->value
                ]);
            });
            fclose($handle);
        }, 'orders_export.csv');
    }

    private function getOrderQuery()
    {
        $query = Order::query()->with(['user', 'courier']);

        if ($this->activeTab !== 'all') {
            $query->where('status', $this->activeTab);
        }

        if ($this->search) {
            $query->where(function ($q) {
                $q->where('order_number', 'like', '%' . $this->search . '%')
                    ->orWhere('name', 'like', '%' . $this->search . '%')
                    ->orWhere('phone', 'like', '%' . $this->search . '%');
            });
        }

        return $query;
    }

    public function render()
    {
        $counts = ['all' => Order::count()];
        foreach (OrderStatus::cases() as $status) {
            $counts[$status->value] = Order::where('status', $status->value)->count();
        }

        return view('livewire.admin.orders.index', [
            'orders' => $this->getOrderQuery()
                ->orderBy($this->sortField, $this->sortDirection)
                ->paginate($this->perPage),
            'counts' => $counts,
            'orderStatuses' => OrderStatus::cases(),
        ]);
    }
}
