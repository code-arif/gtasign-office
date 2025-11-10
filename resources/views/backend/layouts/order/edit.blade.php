<!DOCTYPE html>
<html>

<head>
    <meta charset="utf-8">
    <title>Invoice #{{ $order->id }}</title>
    <style>
        body {
            font-family: DejaVu Sans, sans-serif;
        }

        .invoice-box {
            max-width: 800px;
            margin: auto;
            margin-top: 100px;
            padding: 30px;
            border: 1px solid #eee;
        }

        table {
            width: 100%;
            line-height: inherit;
            text-align: left;
            border-collapse: collapse;
        }

        table td {
            padding: 5px;
            vertical-align: top;
        }

        table th {
            background: #eee;
            padding: 10px;
            border-bottom: 1px solid #ddd;
        }

        .total {
            font-weight: bold;
        }

        .text-right {
            text-align: right;
        }
    </style>
</head>

<body>
    <div class="invoice-box">
        <h2>Invoice #{{ $order->id }}</h2>
        <p>Date: {{ $order->created_at->format('d M, Y') }}</p>
        <p>Customer: {{ $order->user->name ?? 'Guest' }}</p>

        <table>
            <thead>
                <tr>
                    <th>Product</th>
                    <th>Code</th>
                    <th>Quantity</th>
                    <th>Price</th>
                    <th>Discount</th>
                    <th>Total</th>
                </tr>
            </thead>
            <tbody>
                @php
                    $subtotal = 0;
                    $totalDiscount = 0;
                @endphp

                @foreach ($order->items as $item)
                    @php
                        $subtotal += $item->price * $item->quantity;
                        $totalDiscount += $item->discount;
                    @endphp
                    <tr>
                        <td>{{ $item->product_item->productModel->name ?? '-' }}</td>
                        <td>{{ $item->product_item->code ?? '-' }}</td>
                        <td>{{ $item->quantity }}</td>
                        <td>{{ number_format($item->price, 2) }}</td>
                        <td>{{ number_format($item->discount, 2) }}</td>
                        <td>{{ number_format($item->total, 2) }}</td>
                    </tr>
                @endforeach


                <tr>
                    <td colspan="5" class="text-right total">Subtotal</td>
                    <td class="total">{{ number_format($subtotal, 2) }}</td>
                </tr>
                <tr>
                    <td colspan="5" class="text-right total">Discount</td>
                    <td class="total">-{{ number_format($totalDiscount, 2) }}</td>
                </tr>
                <tr>
                    <td colspan="5" class="text-right total">Total Amount</td>
                    <td class="total">{{ number_format($order->total_amount, 2) }}</td>
                </tr>
            </tbody>
        </table>
    </div>
</body>

</html>
