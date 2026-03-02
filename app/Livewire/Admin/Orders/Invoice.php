<?php

namespace App\Livewire\Orders;

use App\Models\Order;
use Livewire\Component;
use Barryvdh\DomPDF\Facade\Pdf;

class Invoice extends Component
{
    // 1. Declare the property here so Livewire can "see" it
    public $order;

    public function mount($orderId)
    {
        // 2. Fetch the order once when the page loads
        $this->order = Order::with([
            'user',
            'vendor',
            'orderItems.product',
            'shippingCity',
            'shippingState',
            'shippingCountry'
        ])->findOrFail($orderId);
    }

    public function downloadPDF()
    {
        // Use the already loaded $this->order
        $data = ['order' => $this->order];

        $pdf = Pdf::loadView('pages.orders.invoice-pdf', [
            'order' => $this->order
        ]);

        // Add these options to ensure Unicode characters are handled
        $pdf->setOption([
            'isHtml5ParserEnabled' => true,
            'isRemoteEnabled' => true,
            'defaultFont' => 'DejaVu Sans', // Force fallback font
            'isFontSubsettingEnabled' => true,
        ]);

        $pdf->setPaper('a4', 'portrait');

        return response()->streamDownload(function () use ($pdf) {
            echo $pdf->output();
        }, "Invoice-{$this->order->order_number}.pdf");
    }

    public function render()
    {
        // 3. No need to pass it in the array anymore, 
        // public properties are automatically available in Blade
        return view('livewire.orders.invoice');
    }
}
