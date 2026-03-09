<?php

namespace App\Livewire\Admin\Orders;

use App\Enums\OrderStatus;
use App\Enums\PaymentStatus;
use App\Models\Order;
use App\Models\Tenant; // Added
use Livewire\Component;
use Livewire\WithPagination;
use Livewire\Attributes\Computed; // Added

class Index extends Component
{
    use WithPagination;

    // --- Table & Search Properties ---
    public $search = '';
    public $perPage = 50;
    public $sortField = 'placed_at';
    public $sortDirection = 'desc';

    // --- Tab & Selection Logic ---
    public $activeTab = 'all';
    public $selectedOrders = [];
    public $selectAll = false;
    public $filterDuplicatePhones = false;

    // --- Dynamic Column Visibility ---
    public $columns = [
        'invoice_no' => true,
        'date' => true,
        'customer' => true,
        'pickup_address' => true,
        'payment_info' => true,
        'delivery_partner' => true,
        'delivery_fee' => true,
        'internal_notes' => true,
    ];
    public $selectAllColumns = true;

    // --- Modals ---
    public $showStatusUpdateModal = false;
    public $updateOrderId;
    public $newOrderStatus;
    public $newPaymentStatus;

    protected $queryString = ['search', 'perPage', 'activeTab'];

    /**
     * Computed property to get current tenant info
     */
    #[Computed]
    public function currentTenant()
    {
        return Tenant::find(session('active_tenant_id'));
    }

    // --- Column Filter Logic ---
    public function updatedSelectAllColumns($value)
    {
        foreach ($this->columns as $key => $val) {
            $this->columns[$key] = $value;
        }
    }

    public function updatedColumns()
    {
        $this->selectAllColumns = !in_array(false, $this->columns);
    }

    // --- Bulk Selection Logic ---
    public function updatedSelectAll($value)
    {
        if ($value) {
            // Updated: Scoped query ensures select all only grabs current tenant's orders
            $this->selectedOrders = Order::query()
                ->when($this->activeTab !== 'all', fn($q) => $q->where('order_status', $this->activeTab))
                ->when($this->search, function ($q) {
                    // Grouped search for tenant security
                    $q->where(function ($inner) {
                        $inner->where('order_number', 'like', '%' . $this->search . '%')
                            ->orWhere('billing_phone', 'like', '%' . $this->search . '%');
                    });
                })
                ->pluck('id')->map(fn($id) => (string) $id)->toArray();
        } else {
            $this->selectedOrders = [];
        }
    }

    // --- Actions ---
    public function approveSelected()
    {
        if (empty($this->selectedOrders)) return;
        // Global scope ensures security here
        Order::whereIn('id', $this->selectedOrders)->update(['order_status' => OrderStatus::Approved]);
        $this->selectedOrders = [];
        $this->selectAll = false;
        session()->flash('message', 'Selected orders have been Approved.');
    }

    public function exportCSV()
    {
        if (empty($this->selectedOrders)) return;
        return response()->streamDownload(function () {
            $handle = fopen('php://output', 'w');
            fputcsv($handle, ['Order No', 'Customer', 'Phone', 'Amount', 'Status']);
            // Global scope handles security during export
            Order::whereIn('id', $this->selectedOrders)->each(function ($o) use ($handle) {
                fputcsv($handle, [$o->order_number, $o->getCustomerNameAttribute(), $o->billing_phone, $o->total_amount, $o->order_status->value]);
            });
            fclose($handle);
        }, 'orders_export.csv');
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
        // findOrFail respects global tenant scope
        $order = Order::findOrFail($orderId);
        $this->updateOrderId = $orderId;
        $this->newOrderStatus = $order->order_status->value;
        $this->newPaymentStatus = $order->payment_status->value;
        $this->showStatusUpdateModal = true;
    }

    public function updateOrderStatus()
    {
        $order = Order::findOrFail($this->updateOrderId);
        $order->update([
            'order_status' => $this->newOrderStatus,
            'payment_status' => $this->newPaymentStatus,
        ]);
        $this->dispatch('close-modal-now');
        $this->showStatusUpdateModal = false;
        session()->flash('message', 'Status updated successfully.');
    }

    public function render()
    {
        // Generate counts for active tenant only
        $counts = ['all' => Order::count()];
        foreach (OrderStatus::cases() as $status) {
            $counts[$status->value] = Order::where('order_status', $status->value)->count();
        }

        $query = Order::query()->with(['user', 'vendor', 'shippingCity']);

        if ($this->activeTab !== 'all') {
            $query->where('order_status', $this->activeTab);
        }

        if ($this->search) {
            $query->where(function ($q) {
                // Grouped OR conditions prevent bypassing the tenant scope
                $q->where('order_number', 'like', '%' . $this->search . '%')
                    ->orWhere('transaction_id', 'like', '%' . $this->search . '%')
                    ->orWhere('billing_phone', 'like', '%' . $this->search . '%')
                    ->orWhereHas('user', fn($uq) => $uq->where('name', 'like', '%' . $this->search . '%'));
            });
        }

        return view('livewire.admin.orders.index', [
            'orders' => $query->orderBy($this->sortField, $this->sortDirection)->paginate($this->perPage),
            'counts' => $counts,
            'orderStatuses' => OrderStatus::cases(),
            'paymentStatuses' => PaymentStatus::cases(),
        ]);
    }
}
