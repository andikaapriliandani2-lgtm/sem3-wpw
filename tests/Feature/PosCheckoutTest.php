<?php

namespace Tests\Feature;

use App\Filament\Resources\Orders\Pages\ListOrders;
use App\Models\Order;
use App\Models\Product;
use App\Models\User;
use App\Services\ReceiptPrinter;
use Filament\Actions\Testing\TestAction;
use Illuminate\Foundation\Testing\LazilyRefreshDatabase;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\URL;
use Livewire\Livewire;
use Tests\TestCase;

class PosCheckoutTest extends TestCase
{
    use LazilyRefreshDatabase;

    public function test_checkout_creates_a_cash_order_and_returns_its_status_page(): void
    {
        $product = Product::factory()->create([
            'name' => 'Cafe Latte',
            'price' => 30000,
        ]);

        $response = $this->postJson(route('checkout'), [
            'customer_name' => 'Ayu',
            'customer_email' => 'ayu@example.com',
            'items' => [
                ['product_id' => $product->id, 'quantity' => 2],
            ],
        ]);

        $response->assertCreated()
            ->assertJsonMissingPath('snap_token');

        $order = Order::query()->firstOrFail();
        $response->assertJsonPath('order_id', $order->order_id);
        $this->assertSame(60000, $order->subtotal);
        $this->assertSame(0, $order->discount);
        $this->assertSame(60000, $order->total);
        $this->assertSame('pending', $order->status);
        $this->assertSame('cash', $order->payment_method);
        $this->assertDatabaseHas('order_items', [
            'order_id' => $order->id,
            'product_id' => $product->id,
            'product_name' => 'Cafe Latte',
            'quantity' => 2,
            'unit_price' => 30000,
            'line_total' => 60000,
        ]);
        $this->assertStringContainsString('/orders/'.$order->order_id, $response->json('order_url'));
    }

    public function test_checkout_rejects_midtrans_as_a_payment_method(): void
    {
        $product = Product::factory()->create();

        $this->postJson(route('checkout'), [
            'customer_name' => 'Ayu',
            'customer_email' => 'ayu@example.com',
            'payment_method' => 'midtrans',
            'items' => [
                ['product_id' => $product->id, 'quantity' => 1],
            ],
        ])->assertUnprocessable()
            ->assertJsonValidationErrors('payment_method');

        $this->assertDatabaseCount('orders', 0);
    }

    public function test_checkout_rejects_a_discount_supplied_by_the_client(): void
    {
        $product = Product::factory()->create(['price' => 20000]);

        $response = $this->postJson(route('checkout'), [
            'customer_name' => 'Ayu',
            'customer_email' => 'ayu@example.com',
            'discount' => 1,
            'items' => [
                ['product_id' => $product->id, 'quantity' => 1],
            ],
        ]);

        $response->assertUnprocessable()
            ->assertJsonPath('errors.discount.0', 'Diskon tidak dapat ditentukan dari browser.');
        $this->assertDatabaseCount('orders', 0);
    }

    public function test_checkout_rejects_a_client_supplied_product_price(): void
    {
        $product = Product::factory()->create(['price' => 20000]);

        $this->postJson(route('checkout'), [
            'customer_name' => 'Ayu',
            'customer_email' => 'ayu@example.com',
            'items' => [
                ['product_id' => $product->id, 'quantity' => 1, 'price' => 1],
            ],
        ])->assertUnprocessable()
            ->assertJsonValidationErrors('items.0');

        $this->assertDatabaseCount('orders', 0);
    }

    public function test_midtrans_notification_endpoint_is_disabled(): void
    {
        $this->postJson('/midtrans/notification', [
            'order_id' => 'KOPI-TEST',
        ])->assertNotFound();
    }

    public function test_cash_order_is_visible_in_the_admin_orders_list(): void
    {
        $admin = User::factory()->create();
        $order = Order::factory()->create([
            'order_id' => 'KOPI-ADMIN-ORDER',
            'payment_method' => 'cash',
        ]);

        $this->actingAs($admin)
            ->get('/admin/orders')
            ->assertOk()
            ->assertSee($order->order_id)
            ->assertSee('Tunai');
    }

    public function test_admin_can_mark_a_cash_order_as_paid(): void
    {
        $admin = User::factory()->create();
        $order = Order::factory()->create([
            'payment_method' => 'cash',
            'status' => 'pending',
        ]);

        $this->actingAs($admin);

        Livewire::test(ListOrders::class)
            ->callAction(TestAction::make('markPaid')->table($order));

        $this->assertDatabaseHas('orders', [
            'id' => $order->id,
            'status' => 'paid',
        ]);
    }

    public function test_signed_order_page_is_required(): void
    {
        $order = Order::factory()->create();

        $this->get(route('orders.show', ['order' => $order]))
            ->assertForbidden();
    }

    public function test_signed_order_page_shows_cash_payment_instructions(): void
    {
        $order = Order::factory()->create(['payment_method' => 'cash']);
        $url = URL::temporarySignedRoute(
            'orders.show',
            now()->addMinutes(10),
            ['order' => $order],
        );

        $this->get($url)
            ->assertOk()
            ->assertSee('Metode pembayaran')
            ->assertSee('Tunai')
            ->assertSee('Silakan bayar tunai di kasir');
    }

    public function test_print_receipt_requires_a_signature(): void
    {
        $order = Order::factory()->create(['status' => 'paid']);

        $this->get(route('orders.print-receipt', ['order' => $order]))
            ->assertForbidden();
    }

    public function test_signed_print_receipt_downloads_a_pdf_only_for_paid_orders(): void
    {
        config(['receipt.driver' => 'pdf']);
        Storage::fake('local');
        $order = Order::factory()->create([
            'order_id' => 'KOPI-TEST-REPRINT',
            'status' => 'paid',
        ]);
        $order->items()->create([
            'product_name' => 'Cafe Latte',
            'quantity' => 1,
            'unit_price' => 30000,
            'line_total' => 30000,
        ]);
        $url = URL::temporarySignedRoute(
            'orders.print-receipt',
            now()->addMinutes(10),
            ['order' => $order],
        );

        $this->get($url)
            ->assertOk()
            ->assertDownload('struk-KOPI-TEST-REPRINT.pdf');

        Storage::disk('local')->assertExists('receipts/KOPI-TEST-REPRINT.pdf');
    }

    public function test_signed_print_receipt_rejects_an_unpaid_order(): void
    {
        config(['receipt.driver' => 'pdf']);
        Storage::fake('local');
        $order = Order::factory()->create([
            'order_id' => 'KOPI-TEST-PENDING-PRINT',
            'status' => 'pending',
        ]);
        $url = URL::temporarySignedRoute(
            'orders.print-receipt',
            now()->addMinutes(10),
            ['order' => $order],
        );

        $this->get($url)->assertNotFound();

        Storage::disk('local')->assertMissing('receipts/KOPI-TEST-PENDING-PRINT.pdf');
    }

    public function test_pdf_receipt_is_created_for_the_simulation_driver(): void
    {
        config(['receipt.driver' => 'pdf']);
        Storage::fake('local');
        $order = Order::factory()->create([
            'order_id' => 'KOPI-TEST-PDF',
            'customer_name' => 'Ayu',
            'subtotal' => 30000,
            'discount' => 5000,
            'total' => 25000,
        ]);
        $order->items()->create([
            'product_name' => 'Cafe Latte',
            'quantity' => 1,
            'unit_price' => 30000,
            'line_total' => 30000,
        ]);

        $receiptPath = app(ReceiptPrinter::class)->print($order);

        Storage::disk('local')->assertExists($receiptPath);
        $this->assertStringStartsWith('%PDF-', Storage::disk('local')->get($receiptPath));
    }

    public function test_file_driver_creates_an_escpos_spool_file(): void
    {
        config(['receipt.driver' => 'file']);
        Storage::fake('local');
        $order = Order::factory()->create([
            'order_id' => 'KOPI-TEST-FILE',
            'total' => 30000,
        ]);
        $order->items()->create([
            'product_name' => 'Cafe Latte',
            'quantity' => 1,
            'unit_price' => 30000,
            'line_total' => 30000,
        ]);
        $spoolPath = storage_path('app/private/receipt-spool/KOPI-TEST-FILE.bin');

        try {
            app(ReceiptPrinter::class)->print($order);

            $this->assertFileExists($spoolPath);
            $this->assertStringContainsString('KOPI KITA', file_get_contents($spoolPath));
            $this->assertStringContainsString('Cafe Latte', file_get_contents($spoolPath));
        } finally {
            if (is_file($spoolPath)) {
                unlink($spoolPath);
            }
        }
    }
}
