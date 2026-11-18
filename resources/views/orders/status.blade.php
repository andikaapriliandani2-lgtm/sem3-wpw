@php
    $statusLabels = [
        'pending' => 'Menunggu pembayaran tunai',
        'paid' => 'Pembayaran berhasil',
        'failed' => 'Pembayaran gagal',
        'refunded' => 'Pembayaran dikembalikan',
    ];
@endphp
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="theme-color" content="#302015">
    <title>Status Pesanan — Kopi Kita</title>
    @vite(['resources/css/app.css'])
</head>
<body class="pos-body font-sans antialiased">
<header class="site-header">
    <div class="site-header-inner">
        <a class="brand" href="{{ route('storefront') }}" aria-label="Kopi Kita">
            <span class="brand-mark" aria-hidden="true">
                <svg viewBox="0 0 24 24" fill="none">
                    <path d="M5 8h12v5.5A5.5 5.5 0 0 1 11.5 19h-1A5.5 5.5 0 0 1 5 13.5V8Z" stroke="currentColor" stroke-width="1.7" stroke-linejoin="round"/>
                    <path d="M17 10h1.5a2.5 2.5 0 0 1 0 5H17M8 4c0 1 1 1.2 1 2.2M12 4c0 1 1 1.2 1 2.2" stroke="currentColor" stroke-width="1.7" stroke-linecap="round"/>
                </svg>
            </span>
            <span>
                <span class="brand-name">KOPI KITA</span>
                <span class="brand-caption">Coffee &amp; good days</span>
            </span>
        </a>
        <a class="back-link status-header-link" href="{{ route('storefront') }}">Kembali ke menu</a>
    </div>
</header>

<main class="status-main">
    <article class="status-card">
        <div class="status-card-header">
            <span class="brand-caption">Kopi Kita · Rincian pesanan</span>
        </div>
        <div class="status-main-content">
            <p class="status-overline">Terima kasih sudah mampir</p>
            <h1 class="status-title">Pesanan kamu</h1>
            <p class="status-copy">Pembayaran dilakukan secara tunai di kasir.</p>
            <span class="order-number">No. pesanan · <strong>{{ $order->order_id }}</strong></span>
            <br>
            <span class="order-number">Metode pembayaran · <strong>Tunai</strong></span>
            <br>
            <span
                id="order-status"
                class="status-pill is-{{ $order->status }}"
                data-status="{{ $order->status }}"
            >{{ $statusLabels[$order->status] ?? ucfirst($order->status) }}</span>
            <p id="status-message" class="status-copy" role="status" aria-live="polite">
                @if ($order->status === 'paid')
                    Pembayaran berhasil. Terima kasih, pesananmu sudah kami terima.
                @elseif ($order->status === 'failed')
                    Pembayaran gagal atau kedaluwarsa. Kamu bisa memilih menu lagi untuk membuat pesanan baru.
                @elseif ($order->status === 'refunded')
                    Pembayaran pesanan ini telah dikembalikan.
                @else
                    Pesanan tercatat. Silakan bayar tunai di kasir; status akan diperbarui setelah pembayaran diterima.
                @endif
            </p>

            <div class="receipt-items" aria-label="Rincian item">
                @foreach ($order->items as $item)
                    <div class="receipt-item">
                        <span>{{ $item->product_name }} <span class="muted">× {{ $item->quantity }}</span></span>
                        <span>Rp{{ number_format($item->line_total, 0, ',', '.') }}</span>
                    </div>
                @endforeach
            </div>

            <hr class="receipt-divider">

            <div class="status-summary">
                <div class="summary-row">
                    <span>Subtotal</span>
                    <strong>Rp{{ number_format($order->subtotal, 0, ',', '.') }}</strong>
                </div>
                <div class="summary-row">
                    <span>Diskon</span>
                    <strong>Rp{{ number_format($order->discount, 0, ',', '.') }}</strong>
                </div>
                <div class="summary-row summary-total">
                    <span>Total pembayaran</span>
                    <strong>Rp{{ number_format($order->total, 0, ',', '.') }}</strong>
                </div>
            </div>

            <div class="status-actions">
                <a class="back-link" href="{{ route('storefront') }}">Kembali ke menu</a>
                <a
                    id="receipt-link"
                    class="pay-button receipt-link"
                    href="{{ $receiptUrl ?? '#' }}"
                    @if (! $receiptUrl) hidden @endif
                    download
                >
                    <svg viewBox="0 0 20 20" fill="none" aria-hidden="true">
                        <path d="M10 3v9m0 0 3.5-3.5M10 12 6.5 8.5M4 13.5V16h12v-2.5" stroke="currentColor" stroke-width="1.6" stroke-linecap="round" stroke-linejoin="round"/>
                    </svg>
                    Unduh struk
                </a>
                <a
                    id="print-receipt-link"
                    class="back-link receipt-link"
                    href="{{ $printUrl ?? '#' }}"
                    @if (! $printUrl) hidden @endif
                >Cetak ulang struk</a>
            </div>
        </div>
    </article>
    <p class="site-footer">Dibuat dengan sepenuh hati, disajikan hangat. · Kopi Kita © {{ now()->year }}</p>
</main>

<script>
    const statusUrl = @json($statusUrl);
    const statusPill = document.getElementById('order-status');
    const statusMessage = document.getElementById('status-message');
    const receiptLink = document.getElementById('receipt-link');
    const printReceiptLink = document.getElementById('print-receipt-link');
    const labels = {
        pending: 'Menunggu pembayaran tunai',
        paid: 'Pembayaran berhasil',
        failed: 'Pembayaran gagal',
        refunded: 'Pembayaran dikembalikan',
    };
    let statusTimer;

    async function refreshStatus() {
        try {
            const response = await fetch(statusUrl, { headers: { 'Accept': 'application/json' } });

            if (!response.ok) {
                throw new Error('Status pesanan tidak dapat diperbarui.');
            }

            const data = await response.json();
            statusPill.dataset.status = data.status;
            statusPill.className = `status-pill is-${data.status}`;
            statusPill.textContent = labels[data.status] || data.status;

            if (data.receipt_url) {
                receiptLink.href = data.receipt_url;
                receiptLink.hidden = false;
            }

            if (data.print_url) {
                printReceiptLink.href = data.print_url;
                printReceiptLink.hidden = false;
            }

            if (data.status === 'pending') {
                statusMessage.textContent = 'Pesanan tercatat. Silakan bayar tunai di kasir; status akan diperbarui setelah pembayaran diterima.';
            } else if (data.status === 'paid') {
                statusMessage.textContent = data.receipt_url
                    ? 'Pembayaran berhasil. Struk sudah siap diunduh.'
                    : 'Pembayaran tunai telah diterima. Struk dapat dicetak dari halaman ini.';
            } else if (data.status === 'failed') {
                statusMessage.textContent = 'Pembayaran gagal atau kedaluwarsa. Kamu bisa membuat pesanan baru dari menu.';
            } else if (data.status === 'refunded') {
                statusMessage.textContent = 'Pembayaran pesanan ini telah dikembalikan.';
            }

            if (data.status !== 'pending') {
                window.clearInterval(statusTimer);
            }
        } catch (error) {
            statusMessage.textContent = error.message;
        }
    }

    if (statusPill.dataset.status === 'pending') {
        statusTimer = window.setInterval(refreshStatus, 3000);
    }
</script>
</body>
</html>
