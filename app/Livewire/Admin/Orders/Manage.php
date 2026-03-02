<?php

namespace App\Livewire\Orders;

use App\Models\Order;
use App\Enums\OrderStatus;
use App\Enums\PaymentStatus;
use Livewire\Component;

class Manage extends Component
{
    public $orderId;
    public $order;

    // Form fields for updating
    public $orderStatus;
    public $paymentStatus;
    public $trackingNumber;

    public function mount($orderId)
    {
        $this->orderId = $orderId;
        $this->loadOrder();
    }

    public function loadOrder()
    {
        $this->order = Order::with([
            'user',
            'vendor',
            'orderItems.product',
            'shippingCity',
            'shippingState',
            'shippingCountry'
        ])->findOrFail($this->orderId);

        $this->orderStatus = $this->order->order_status->value;
        $this->paymentStatus = $this->order->payment_status->value;
        $this->trackingNumber = $this->order->tracking_number;
    }

    public function updateOrder()
    {
        $this->validate([
            'orderStatus' => 'required',
            'paymentStatus' => 'required',
            'trackingNumber' => 'nullable|string|max:100',
        ]);

        $this->order->update([
            'order_status' => $this->orderStatus,
            'payment_status' => $this->paymentStatus,
            'tracking_number' => $this->trackingNumber,
            // Update timestamps based on status
            'shipped_at' => ($this->orderStatus === OrderStatus::Shipped->value) ? now() : $this->order->shipped_at,
            'delivered_at' => ($this->orderStatus === OrderStatus::Delivered->value) ? now() : $this->order->delivered_at,
            'cancelled_at' => ($this->orderStatus === OrderStatus::Cancelled->value) ? now() : $this->order->cancelled_at,
        ]);

        session()->flash('message', 'Order updated successfully!');
        $this->loadOrder(); // Refresh data
    }

    public function render()
    {
        return view('livewire.orders.manage', [
            'orderStatuses' => OrderStatus::cases(),
            'paymentStatuses' => PaymentStatus::cases(),
        ]);
    }
}
