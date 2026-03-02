<div class="container py-5">
    <!-- Print Button (Hidden during print) -->
    <div class="d-flex justify-content-end mb-4 no-print">
        <!-- Download Button -->
        <div class="d-flex justify-content-end mb-4">
            <button wire:click="downloadPDF" class="btn btn-danger shadow-sm px-4">
                <span wire:loading.remove wire:target="downloadPDF">
                    <i class="fas fa-file-pdf me-2"></i> Download PDF
                </span>
                <span wire:loading wire:target="downloadPDF">
                    <span class="spinner-border spinner-border-sm me-2"></span> Generating PDF...
                </span>
            </button>
        </div>
    </div>

    <div class="invoice-box bg-white p-5 shadow-sm border">
        <!-- Header -->
        <div class="row mb-5">
            <div class="col-6">
                <h2 class="text-uppercase fw-bold text-primary">CASH MEMO</h2>
                <p class="mb-1"><strong>Invoice # :</strong> {{ $this->order->order_number }}</p>
                <p class="mb-0"><strong>Date :</strong> {{ $this->order->placed_at->format('d M, Y') }}</p>
            </div>
            <div class="col-6 text-end">
                <h4 class="fw-bold">{{ config('app.name') }}</h4>
                <p class="text-muted small mb-0">House #123, Road #45, Dhanmondi</p>
                <p class="text-muted small mb-0">Dhaka, Bangladesh</p>
                <p class="text-muted small">Phone: +880123456789</p>
            </div>
        </div>

        <hr>

        <!-- Address Section -->
        <div class="row my-5">
            <div class="col-6">
                <h6 class="text-muted text-uppercase small fw-bold">Sold By:</h6>
                <p class="mb-1 fw-bold text-dark">{{ $this->order->vendor->name ?? 'Main Store' }}</p>
                <p class="mb-1 small text-muted">Phone: {{ $this->order->vendor->phone ?? 'N/A' }}</p>
            </div>
            <div class="col-6 text-end">
                <h6 class="text-muted text-uppercase small fw-bold">Customer Details:</h6>
                <p class="mb-1 fw-bold text-dark">{{ $this->order->shipping_first_name }} {{ $this->order->shipping_last_name }}</p>
                <p class="mb-1 small">{{ $this->order->shipping_address_line_1 }}</p>
                <p class="mb-1 small">{{ $this->order->shippingCity?->name }}, {{ $this->order->shippingState?->name }} - {{ $this->order->shipping_zip_code }}</p>
                <p class="mb-0 small"><strong>Phone:</strong> {{ $this->order->shipping_phone }}</p>
            </div>
        </div>

        <!-- Items Table -->
        <div class="table-responsive mb-4">
            <table class="table table-bordered">
                <thead class="bg-light">
                    <tr>
                        <th class="py-2">SL.</th>
                        <th class="py-2">Description</th>
                        <th class="py-2 text-center">Unit Price</th>
                        <th class="py-2 text-center">Qty</th>
                        <th class="py-2 text-end">Total</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($this->order->orderItems as $index => $item)
                    <tr>
                        <td class="text-center">{{ $index + 1 }}</td>
                        <td>
                            <span class="fw-bold">{{ $item->item_name }}</span>
                            @if($item->item_attributes)
                            <div class="small text-muted">
                                @foreach($item->item_attributes as $key => $val)
                                {{ $key }}: {{ $val }}@if(!$loop->last), @endif
                                @endforeach
                            </div>
                            @endif
                        </td>
                        <td class="text-center">৳{{ number_format($item->unit_price, 2) }}</td>
                        <td class="text-center">{{ $item->quantity }}</td>
                        <td class="text-end">৳{{ number_format($item->subtotal, 2) }}</td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>

        <!-- Totals -->
        <div class="row justify-content-end">
            <div class="col-md-5">
                <table class="table table-borderless">
                    <tr>
                        <td class="py-1">Subtotal</td>
                        <td class="py-1 text-end">৳{{ number_format($this->order->subtotal, 2) }}</td>
                    </tr>
                    <tr>
                        <td class="py-1">Shipping Cost</td>
                        <td class="py-1 text-end">৳{{ number_format($this->order->shipping_cost, 2) }}</td>
                    </tr>
                    <tr class="border-top">
                        <td class="py-2 fw-bold fs-5">Grand Total</td>
                        <td class="py-2 fw-bold fs-5 text-end text-primary">৳{{ number_format($this->order->total_amount, 2) }}</td>
                    </tr>
                </table>
            </div>
        </div>

        <!-- Payment Info -->
        <div class="mt-5 p-3 bg-light rounded">
            <div class="row">
                <div class="col-6">
                    <p class="mb-1 small"><strong>Payment Method:</strong> {{ $this->order->payment_method_name }}</p>
                    @if($this->order->transaction_id)
                    <p class="mb-0 small"><strong>TxID:</strong> {{ $this->order->transaction_id }}</p>
                    @endif
                </div>
                <div class="col-6 text-end">
                    <p class="mb-0 small"><strong>Payment Status:</strong> {{ $this->order->payment_status->label() }}</p>
                </div>
            </div>
        </div>

        <!-- Footer -->
        <div class="row mt-5 pt-5">
            <div class="col-6 text-center">
                <div class="border-top pt-2 mx-auto" style="width: 150px;">Customer Signature</div>
            </div>
            <div class="col-6 text-center">
                <div class="border-top pt-2 mx-auto" style="width: 150px;">Authorized By</div>
            </div>
        </div>

        <div class="text-center mt-5">
            <p class="text-muted small">Thank you for shopping with us!</p>
        </div>
    </div>

    <style>
        @media print {
            .no-print {
                display: none !important;
            }

            .container {
                width: 100% !important;
                max-width: 100% !important;
                margin: 0 !important;
                padding: 0 !important;
            }

            .invoice-box {
                border: none !important;
                box-shadow: none !important;
            }

            body {
                background: #fff !important;
            }
        }

        .invoice-box {
            min-height: 842px;
            /* Standard A4 Height approx */
        }
    </style>
</div>