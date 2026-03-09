<!DOCTYPE html>
<html>

<head>
    {{-- CRITICAL: UTF-8 encoding for Unicode characters like the Taka sign --}}
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
    <style>
        /* DejaVu Sans is the only built-in font that supports the Taka (৳) symbol */
        /* 1. Register the custom font */
        html,
        body,
        div,
        p,
        span,
        table,
        thead,
        tbody,
        tfoot,
        tr,
        th,
        td {
            font-family: 'DejaVu Sans', sans-serif !important;
        }

        body {
            font-family: 'DejaVu Sans', sans-serif;
            font-size: 12px;
        }

        body {
            margin: 0;
            padding: 0;
            font-size: 12px;
        }

        .invoice-box {
            padding: 30px;
        }

        .table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 20px;
        }

        .table th,
        .table td {
            border: 1px solid #eee;
            padding: 10px;
            text-align: left;
        }

        .table th {
            background-color: #f8f9fa;
            font-weight: bold;
        }

        .text-right {
            text-align: right;
        }

        .text-center {
            text-align: center;
        }

        .header-table {
            width: 100%;
            border-bottom: 1px solid #ddd;
            padding-bottom: 20px;
        }

        .info-table {
            width: 100%;
            margin: 20px 0;
        }

        .totals-table {
            width: 40%;
            margin-left: auto;
            margin-top: 20px;
        }

        .fw-bold {
            font-weight: bold;
        }

        .text-primary {
            color: #007bff;
        }

        .badge {
            background: #f1f1f1;
            padding: 2px 4px;
            font-size: 10px;
            border: 1px solid #ccc;
        }

        .footer {
            margin-top: 50px;
            text-align: center;
            font-size: 10px;
            color: #777;
        }

        .signature-section {
            margin-top: 70px;
            width: 100%;
        }

        .signature-box {
            border-top: 1px solid #333;
            width: 150px;
            text-align: center;
            padding-top: 5px;
        }

        .taka-sign {
            font-family: 'DejaVu Sans', sans-serif !important;
            display: inline-block;
        }
    </style>
</head>

<body>
    <div class="invoice-box">
        <!-- Brand Header -->
        <table class="header-table">
            <tr>
                <td>
                    <h1 style="color: #007bff; margin: 0;">INVOICE</h1>
                    <p style="margin: 5px 0;">Order #: {{ $order->order_number }}</p>
                    <p style="margin: 0;">Date: {{ $order->placed_at->format('d M, Y') }}</p>
                </td>
                <td class="text-right">
                    <h2 style="margin: 0;">{{ config('app.name') }}</h2>
                    <p style="margin: 5px 0;">Dhanmondi, Dhaka, Bangladesh</p>
                    <p style="margin: 0;">Phone: +880123456789</p>
                </td>
            </tr>
        </table>

        <!-- Shipping & Vendor Info -->
        <table class="info-table">
            <tr>
                <td style="width: 50%; vertical-align: top;">
                    <p class="fw-bold" style="text-decoration: underline; margin-bottom: 5px;">BILL TO:</p>
                    <strong>{{ $order->shipping_first_name }} {{ $order->shipping_last_name }}</strong><br>
                    {{ $order->shipping_address_line_1 }}<br>
                    {{ $order->shippingCity?->name }}, {{ $order->shippingState?->name }}<br>
                    Phone: {{ $order->shipping_phone }}
                </td>
                <td style="width: 50%; vertical-align: top; text-align: right;">
                    <p class="fw-bold" style="text-decoration: underline; margin-bottom: 5px;">SOLD BY:</p>
                    <strong>{{ $order->vendor->name ?? 'Main Store' }}</strong><br>
                    Phone: {{ $order->vendor->phone ?? 'N/A' }}
                </td>
            </tr>
        </table>

        <!-- Order Items -->
        <table class="table">
            <thead>
                <tr>
                    <th class="text-center" width="5%">#</th>
                    <th>Description</th>
                    <th class="text-center" width="15%">Price</th>
                    <th class="text-center" width="10%">Qty</th>
                    <th class="text-right" width="20%">Total</th>
                </tr>
            </thead>
            <tbody>
                @foreach($order->orderItems as $index => $item)
                <tr>
                    <td class="text-center">{{ $index + 1 }}</td>
                    <td>
                        <div class="fw-bold">{{ $item->item_name }}</div>
                        @if($item->item_attributes)
                        <div style="margin-top: 4px;">
                            @foreach($item->item_attributes as $key => $val)
                            <span class="badge">{{ $key }}: {{ $val }}</span>
                            @endforeach
                        </div>
                        @endif
                    </td>
                    {{-- Use HTML Entity &#2547; for Taka symbol for maximum compatibility --}}
                    <td class="text-center"><span class="taka-sign">&#2547;</span>{{ number_format($item->unit_price, 2) }}</td>
                    <td class="text-center">{{ $item->quantity }}</td>
                    <td class="text-right"><span class="taka-sign">&#2547;</span>{{ number_format($item->subtotal, 2) }}</td>
                </tr>
                @endforeach
            </tbody>
        </table>

        <!-- Summary -->
        <table class="totals-table">
            <tr>
                <td>Subtotal:</td>
                <td class="text-right"><span class="taka-sign">&#2547;</span>{{ number_format($order->subtotal, 2) }}</td>
            </tr>
            <tr>
                <td>Shipping:</td>
                <td class="text-right"><span class="taka-sign">&#2547;</span>{{ number_format($order->shipping_cost, 2) }}</td>
            </tr>
            <tr>
                <td class="fw-bold text-primary" style="font-size: 14px; border-top: 1px solid #ddd;">Grand Total:</td>
                <td class="text-right fw-bold text-primary" style="font-size: 14px; border-top: 1px solid #ddd;"><span class="taka-sign">&#2547;</span>{{ number_format($order->total_amount, 2) }}</td>
            </tr>
        </table>

        <!-- Payment Details -->
        <div style="margin-top: 30px; background: #f9f9f9; padding: 10px;">
            <p style="margin: 0;"><strong>Payment Method:</strong> {{ $order->payment_method_name }}</p>
            @if($order->transaction_id)
            <p style="margin: 5px 0 0 0;"><strong>Transaction ID:</strong> <span style="color: #d9534f;">{{ $order->transaction_id }}</span></p>
            @endif
        </div>

        <!-- Signature Lines -->
        <table class="signature-section">
            <tr>
                <td width="50%">
                    <div class="signature-box" style="margin-left: 0;">Customer Signature</div>
                </td>
                <td width="50%" align="right">
                    <div class="signature-box" style="margin-right: 0;">Authorized Signature</div>
                </td>
            </tr>
        </table>

        <div class="footer">
            Thank you for your purchase!<br>
            This is a computer-generated invoice and requires no physical signature.
        </div>
    </div>
</body>

</html>