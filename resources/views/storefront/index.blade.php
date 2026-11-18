<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Kopi Kita — Kasir</title>
    @vite(['resources/css/app.css'])
</head>
<body class="pos-body font-sans antialiased">
<header class="site-header">
    <div class="site-header-inner">
        <a class="brand" href="{{ route('storefront') }}" aria-label="Kopi Kita, kembali ke kasir">
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
        <div class="header-meta"><span class="open-dot"></span><span>Siap menyeduh untukmu</span></div>
    </div>
</header>

<main class="pos-main">
    <section class="hero" aria-labelledby="hero-title">
        <div class="hero-copy">
            <p class="hero-eyebrow"><span class="open-dot"></span> Seduhan hari ini</p>
            <h1 id="hero-title" class="hero-title">Berhenti sejenak,<br>nikmati <em>kopi favoritmu.</em></h1>
            <p class="hero-description">Dibuat dengan biji pilihan dan disajikan hangat, biar harimu terasa lebih baik.</p>
        </div>
        <div class="hero-art" aria-hidden="true">
            <div class="hero-cup">
                <svg viewBox="0 0 40 40" fill="none">
                    <path d="M8 18c3.5-5 8.5 5 12 0s8.5 5 12 0M9 22c3.2-4 7.8 4 11 0s7.8 4 11 0" stroke="currentColor" stroke-width="2.1" stroke-linecap="round"/>
                </svg>
            </div>
        </div>
    </section>

    @php
        $artClasses = ['product-art-1', 'product-art-2', 'product-art-3', 'product-art-4'];
    @endphp

    <div class="menu-heading">
        <div>
            <p class="section-eyebrow">Dari barista kami</p>
            <h2 class="section-title">Temukan menu favoritmu</h2>
            <p class="section-description">Diracik segar, khusus untuk menemani harimu.</p>
        </div>
        <label class="search-box">
            <svg viewBox="0 0 24 24" fill="none" aria-hidden="true">
                <circle cx="10.8" cy="10.8" r="6.8" stroke="currentColor" stroke-width="1.7"/>
                <path d="m16 16 4.3 4.3" stroke="currentColor" stroke-width="1.7" stroke-linecap="round"/>
            </svg>
            <input id="product-search" class="search-input" type="search" placeholder="Cari menu..." aria-label="Cari menu">
        </label>
    </div>

    <form id="checkout-form">
        <div class="pos-layout">
            <section class="product-grid" aria-label="Daftar produk">
                @forelse ($products as $product)
                    <article
                        class="product-card"
                        data-product-card
                        data-product-name="{{ strtolower($product->name) }}"
                    >
                        <div class="product-art {{ $artClasses[($loop->iteration - 1) % count($artClasses)] }}" aria-hidden="true">
                            <span class="art-label">KOPI KITA · FRESH BREW</span>
                            <span class="drink-cup">
                                <svg viewBox="0 0 24 24" fill="none">
                                    <path d="M7 12c2-3 4 3 6 0s4 3 6 0M7 15c2-3 4 3 6 0s4 3 6 0" stroke="currentColor" stroke-width="1.5" stroke-linecap="round"/>
                                </svg>
                            </span>
                        </div>
                        <div class="product-info">
                            <h3 class="product-name">{{ $product->name }}</h3>
                            <p class="product-description">{{ $product->description }}</p>
                            <div class="product-footer">
                                <span class="product-price">Rp{{ number_format($product->price, 0, ',', '.') }}</span>
                                <div class="quantity-control" aria-label="Jumlah {{ $product->name }}">
                                    <button
                                        class="quantity-step"
                                        type="button"
                                        data-step="-1"
                                        aria-label="Kurangi {{ $product->name }}"
                                    >
                                        <svg viewBox="0 0 20 20" fill="none" aria-hidden="true">
                                            <path d="M4.5 10h11" stroke="currentColor" stroke-width="1.8" stroke-linecap="round"/>
                                        </svg>
                                    </button>
                                    <input
                                        class="quantity-value quantity"
                                        type="number"
                                        min="0"
                                        max="25"
                                        value="0"
                                        aria-label="Jumlah {{ $product->name }}"
                                        data-product-id="{{ $product->id }}"
                                        data-price="{{ $product->price }}"
                                    >
                                    <button
                                        class="quantity-step"
                                        type="button"
                                        data-step="1"
                                        aria-label="Tambah {{ $product->name }}"
                                    >
                                        <svg viewBox="0 0 20 20" fill="none" aria-hidden="true">
                                            <path d="M10 4.5v11m-5.5-5.5h11" stroke="currentColor" stroke-width="1.8" stroke-linecap="round"/>
                                        </svg>
                                    </button>
                                </div>
                            </div>
                        </div>
                    </article>
                @empty
                    <p class="empty-search">Belum ada menu yang tersedia. Coba lagi nanti.</p>
                @endforelse
                <p id="empty-search" class="empty-search" hidden>Menu tidak ditemukan. Coba kata kunci lain.</p>
            </section>

            <aside class="cart-panel" aria-label="Pesanan">
                <div class="cart-heading">
                    <div>
                        <h2 class="cart-title">Pesanan kamu</h2>
                        <p class="cart-subtitle">Sudah siap memilih?</p>
                    </div>
                    <span id="cart-count" class="cart-count" aria-label="Jumlah item">0</span>
                </div>

                <div id="cart-items" class="cart-items" aria-live="polite">
                    <div id="empty-cart" class="cart-empty">
                        <svg viewBox="0 0 24 24" fill="none" aria-hidden="true">
                            <path d="M5 8h14l-1.2 11H6.2L5 8Z" stroke="currentColor" stroke-width="1.6" stroke-linejoin="round"/>
                            <path d="M9 9V6a3 3 0 0 1 6 0v3" stroke="currentColor" stroke-width="1.6" stroke-linecap="round"/>
                        </svg>
                        <p>Pesananmu masih kosong</p>
                    </div>
                </div>

                <div class="checkout-fields">
                    <label>
                        <span class="field-label">Nama pelanggan</span>
                        <input
                            id="customer-name"
                            class="customer-input"
                            name="customer_name"
                            type="text"
                            maxlength="120"
                            placeholder="Nama kamu"
                            autocomplete="name"
                            required
                        >
                    </label>
                    <label>
                        <span class="field-label">Email untuk pesanan</span>
                        <input
                            id="customer-email"
                            class="customer-input"
                            name="customer_email"
                            type="email"
                            maxlength="255"
                            placeholder="nama@email.com"
                            autocomplete="email"
                            required
                        >
                    </label>
                </div>

                <div class="cart-summary" aria-live="polite">
                    <div class="summary-row"><span>Subtotal</span><strong id="subtotal">Rp0</strong></div>
                    <div class="summary-row"><span>Diskon</span><strong id="discount-total">Rp0</strong></div>
                    <div class="summary-row summary-total"><span>Total pembayaran</span><strong id="grand-total">Rp0</strong></div>
                </div>

                <div class="checkout-footer">
                    <p class="payment-note">Metode pembayaran: tunai di kasir.</p>
                    <button id="pay-button" class="pay-button" type="submit" disabled>
                        Buat pesanan
                        <svg viewBox="0 0 20 20" fill="none" aria-hidden="true">
                            <path d="M4 10h12m-5-5 5 5-5 5" stroke="currentColor" stroke-width="1.7" stroke-linecap="round" stroke-linejoin="round"/>
                        </svg>
                    </button>
                    <p id="notice" class="checkout-notice" role="status" aria-live="polite"></p>
                </div>
            </aside>
        </div>
    </form>

    <footer class="site-footer">Dibuat dengan sepenuh hati, disajikan hangat. · Kopi Kita © {{ now()->year }}</footer>
</main>

<script>
    const checkoutForm = document.getElementById('checkout-form');
    const quantityInputs = [...document.querySelectorAll('.quantity')];
    const productCards = [...document.querySelectorAll('[data-product-card]')];
    const cartItems = document.getElementById('cart-items');
    const emptyCart = document.getElementById('empty-cart');
    const notice = document.getElementById('notice');
    const payButton = document.getElementById('pay-button');
    const currency = new Intl.NumberFormat('id-ID', { style: 'currency', currency: 'IDR', maximumFractionDigits: 0 });
    const formatCurrency = (amount) => currency.format(amount);

    function calculateSubtotal() {
        return quantityInputs.reduce((total, input) => {
            return total + Number(input.value || 0) * Number(input.dataset.price);
        }, 0);
    }

    function updateCart() {
        cartItems.querySelectorAll('[data-cart-item]').forEach((item) => item.remove());

        const selectedItems = quantityInputs
            .filter((input) => Number(input.value) > 0)
            .map((input) => {
                const card = input.closest('[data-product-card]');
                return {
                    card,
                    input,
                    name: card.querySelector('.product-name').textContent.trim(),
                    price: Number(input.dataset.price),
                    quantity: Number(input.value),
                };
            });
        const totalQuantity = selectedItems.reduce((total, item) => total + item.quantity, 0);
        const subtotal = calculateSubtotal();

        emptyCart.hidden = selectedItems.length > 0;
        document.getElementById('cart-count').textContent = totalQuantity;
        document.getElementById('subtotal').textContent = formatCurrency(subtotal);
        document.getElementById('discount-total').textContent = formatCurrency(0);
        document.getElementById('grand-total').textContent = formatCurrency(subtotal);

        selectedItems.forEach((item) => {
            const row = document.createElement('div');
            const details = document.createElement('div');
            const name = document.createElement('p');
            const quantity = document.createElement('p');
            const total = document.createElement('span');

            row.className = 'cart-item';
            row.dataset.cartItem = '';
            name.className = 'cart-item-name';
            name.textContent = item.name;
            quantity.className = 'cart-item-quantity';
            quantity.textContent = `${item.quantity} × ${formatCurrency(item.price)}`;
            total.className = 'cart-item-total';
            total.textContent = formatCurrency(item.quantity * item.price);
            details.append(name, quantity);
            row.append(details, total);
            cartItems.append(row);
            item.card.classList.toggle('is-selected', item.quantity > 0);
        });

        productCards
            .filter((card) => !selectedItems.some((item) => item.card === card))
            .forEach((card) => card.classList.remove('is-selected'));

        if (payButton) {
            payButton.disabled = totalQuantity === 0;
        }
    }

    quantityInputs.forEach((input) => {
        input.addEventListener('input', () => {
            const quantity = Math.min(25, Math.max(0, Number(input.value) || 0));
            input.value = quantity;
            updateCart();
        });
    });

    document.querySelectorAll('.quantity-step').forEach((button) => {
        button.addEventListener('click', () => {
            const input = button.closest('.quantity-control').querySelector('.quantity');
            input.value = Math.min(25, Math.max(0, Number(input.value) + Number(button.dataset.step)));
            updateCart();
        });
    });

    document.getElementById('product-search').addEventListener('input', (event) => {
        const searchTerm = event.target.value.trim().toLocaleLowerCase('id');
        let visibleCount = 0;

        productCards.forEach((card) => {
            const matches = card.dataset.productName.includes(searchTerm);
            card.hidden = !matches;
            visibleCount += Number(matches);
        });

        document.getElementById('empty-search').hidden = visibleCount > 0;
    });

    checkoutForm.addEventListener('submit', async (event) => {
        event.preventDefault();
        notice.textContent = '';

        const items = quantityInputs
            .map((input) => ({
                product_id: Number(input.dataset.productId),
                quantity: Number(input.value || 0),
            }))
            .filter((item) => item.quantity > 0);

        if (items.length === 0) {
            notice.textContent = 'Pilih setidaknya satu menu.';
            return;
        }

        payButton.disabled = true;
        payButton.textContent = 'Menyimpan pesanan...';

        try {
            const response = await fetch(@json(route('checkout')), {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'Accept': 'application/json',
                    'X-CSRF-TOKEN': @json(csrf_token()),
                },
                body: JSON.stringify({
                    customer_name: document.getElementById('customer-name').value,
                    customer_email: document.getElementById('customer-email').value,
                    items,
                }),
            });
            const result = await response.json();

            if (!response.ok) {
                const validationMessage = Object.values(result.errors || {}).flat()[0];
                throw new Error(validationMessage || result.message || 'Checkout gagal.');
            }

            window.location.href = result.order_url;
        } catch (error) {
            notice.textContent = error.message;
        } finally {
            payButton.innerHTML = 'Buat pesanan <svg viewBox="0 0 20 20" fill="none" aria-hidden="true"><path d="M4 10h12m-5-5 5 5-5 5" stroke="currentColor" stroke-width="1.7" stroke-linecap="round" stroke-linejoin="round"/></svg>';
            payButton.disabled = quantityInputs.every((input) => Number(input.value) === 0);
        }
    });

    updateCart();
</script>
</body>
</html>
