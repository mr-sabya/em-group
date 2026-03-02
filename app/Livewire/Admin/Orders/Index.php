<?php

namespace App\Livewire\Orders;

use App\Enums\OrderStatus;
use App\Enums\PaymentStatus;
use App\Models\Order;
use Livewire\Component;
use Livewire\WithPagination;

class Index extends Component
{
    use WithPagination;

    public $search = '';
    public $perPage = 10;
    public $sortField = 'placed_at';
    public $sortDirection = 'desc';

    public $showOrderDetailsModal = false;
    public $selectedOrderId;

    public $showStatusUpdateModal = false;
    public $updateOrderId;
    public $newOrderStatus;
    public $newPaymentStatus;

    protected $queryString = ['search', 'perPage', 'sortField', 'sortDirection'];

    public function updatingSearch()
    {
        $this->resetPage();
    }

    public function sortBy($field)
    {
        if ($this->sortField === $field) {
            $this->sortDirection = $this->sortDirection === 'asc' ? 'desc' : 'asc';
        } else {
            $this->sortDirection = 'asc';
        }
        $this->sortField = $field;
    }

    public function viewOrderDetails($orderId)
    {
        $this->selectedOrderId = $orderId;
        $this->showOrderDetailsModal = true;
    }

    public function openStatusUpdateModal($orderId)
    {
        $order = Order::findOrFail($orderId);
        $this->updateOrderId = $orderId;
        $this->newOrderStatus = $order->order_status->value;
        $this->newPaymentStatus = $order->payment_status->value;
        $this->showStatusUpdateModal = true;
    }

    public function updateOrderStatus()
    {
        $this->validate([
            'newOrderStatus' => 'required',
            'newPaymentStatus' => 'required',
        ]);

        $order = Order::findOrFail($this->updateOrderId);
        $order->update([
            'order_status' => $this->newOrderStatus,
            'payment_status' => $this->newPaymentStatus,
        ]);

        session()->flash('message', 'Order status updated successfully.');

        // Dispatch event to close modal via Alpine
        $this->dispatch('close-modal-now');
        $this->showStatusUpdateModal = false;
    }

    public function closeStatusUpdateModal()
    {
        $this->showStatusUpdateModal = false;
        $this->updateOrderId = null;
    }

    public function closeOrderDetailsModal()
    {
        $this->showOrderDetailsModal = false;
        $this->selectedOrderId = null;
    }

    public function render()
    {
        $orders = Order::query()
            ->with(['user', 'vendor'])
            ->when($this->search, function ($query) {
                $query->where(function ($q) {
                    $q->where('order_number', 'like', '%' . $this->search . '%')
                        ->orWhere('transaction_id', 'like', '%' . $this->search . '%')
                        ->orWhere('payment_phone_number', 'like', '%' . $this->search . '%')
                        ->orWhere('billing_email', 'like', '%' . $this->search . '%')
                        ->orWhereHas('user', function ($uq) {
                            $uq->where('name', 'like', '%' . $this->search . '%')
                                ->orWhere('phone', 'like', '%' . $this->search . '%');
                        })
                        ->orWhereHas('vendor', function ($vq) {
                            $vq->where('name', 'like', '%' . $this->search . '%');
                        });
                });
            })
            ->orderBy($this->sortField, $this->sortDirection)
            ->paginate($this->perPage);

        return view('livewire.orders.index', [
            'orders' => $orders,
            'orderStatuses' => OrderStatus::cases(),
            'paymentStatuses' => PaymentStatus::cases(),
        ]);
    }
}
