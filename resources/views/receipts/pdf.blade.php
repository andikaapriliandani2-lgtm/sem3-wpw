<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <style>
        body { color: #222; font-family: DejaVu Sans, sans-serif; font-size: 11px; }
        h1, .center { text-align: center; }
        h1 { margin-bottom: 4px; font-size: 18px; }
        .muted { color: #666; }
        table { width: 100%; margin-top: 16px; border-collapse: collapse; }
        th, td { padding: 7px 3px; border-bottom: 1px solid #ddd; text-align: left; }
        .number { text-align: right; }
        .totals { margin-top: 14px; }
        .totals div { display: flex; justify-content: space-between; margin: 6px 0; }
        .grand-total { padding-top: 8px; border-top: 1px solid #222; font-weight: bold; }
    </style>
</head>
<body>
    <h1>Kopi Kita</h1>
    <p class="center muted">Struk transaksi</p>
    <p>No. pesanan: {{ $order->order_id }}<br>
        Tanggal: {{ $order->created_at->format('d/m/Y H:i') }}<br>
        Pelanggan: {{ $order->customer_name }}<br>
        Pembayaran: {{ $order->payment_method === 'cash' ? 'Tunai' : $order->payment_method }}</p>
    <table>
        <thead>
            <tr>
                <th>Item</th>
                <th class="number">Qty</th>
                <th class="number">Harga</th>
                <th class="number">Jumlah</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($order->items as $item)
                <tr>
                    <td>{{ $item->product_name }}</td>
                    <td class="number">{{ $item->quantity }}</td>
                    <td class="number">Rp{{ number_format($item->unit_price, 0, ',', '.') }}</td>
                    <td class="number">Rp{{ number_format($item->line_total, 0, ',', '.') }}</td>
                </tr>
            @endforeach
        </tbody>
    </table>
    <div class="totals">
        <div><span>Subtotal</span><span>Rp{{ number_format($order->subtotal, 0, ',', '.') }}</span></div>
        <div><span>Diskon</span><span>Rp{{ number_format($order->discount, 0, ',', '.') }}</span></div>
        <div class="grand-total"><span>Total pembayaran</span><span>Rp{{ number_format($order->total, 0, ',', '.') }}</span></div>
    </div>
    <p class="center">Terima kasih telah berkunjung!</p>
</body>
</html>
