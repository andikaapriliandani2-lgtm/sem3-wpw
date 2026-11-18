<?php

namespace App\Http\Controllers;

use App\Models\Order;
use App\Models\Product;
use App\Services\ReceiptPrinter;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\StreamedResponse;

class CheckoutController extends Controller
{
    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'customer_name' => ['required', 'string', 'max:120'],
            'customer_email' => ['required', 'email', 'max:255'],
            'items' => ['required', 'array', 'min:1', 'max:30'],
            'items.*' => ['required', 'array:product_id,quantity'],
            'items.*.product_id' => ['required', 'integer', 'distinct', 'exists:products,id'],
            'items.*.quantity' => ['required', 'integer', 'min:1', 'max:25'],
            'payment_method' => ['sometimes', 'string', Rule::in(['cash'])],
            'discount' => ['prohibited'],
        ], [
            'discount.prohibited' => 'Diskon tidak dapat ditentukan dari browser.',
        ]);

        $requestedItems = collect($validated['items'])->keyBy('product_id');
        $products = Product::query()
            ->where('active', true)
            ->whereIn('id', $requestedItems->keys())
            ->get()
            ->keyBy('id');

        if ($products->count() !== $requestedItems->count()) {
            throw ValidationException::withMessages([
                'items' => 'Satu atau lebih produk tidak tersedia.',
            ]);
        }

        $lineItems = $requestedItems->map(function (array $item, string $productId) use ($products): array {
            $product = $products->get($productId);

            return [
                'product' => $product,
                'quantity' => (int) $item['quantity'],
                'line_total' => $product->price * (int) $item['quantity'],
            ];
        });

        $subtotal = $lineItems->sum('line_total');
        $discount = 0;

        $order = DB::transaction(function () use ($validated, $lineItems, $subtotal, $discount): Order {
            $order = Order::query()->create([
                'order_id' => 'KOPI-'.Str::upper((string) Str::uuid()),
                'customer_name' => $validated['customer_name'],
                'customer_email' => $validated['customer_email'],
                'status' => 'pending',
                'payment_method' => 'cash',
                'subtotal' => $subtotal,
                'discount' => $discount,
                'total' => $subtotal - $discount,
            ]);

            foreach ($lineItems as $lineItem) {
                $product = $lineItem['product'];
                $order->items()->create([
                    'product_id' => $product->id,
                    'product_name' => $product->name,
                    'quantity' => $lineItem['quantity'],
                    'unit_price' => $product->price,
                    'line_total' => $lineItem['line_total'],
                ]);
            }

            return $order;
        });

        return response()->json([
            'order_id' => $order->order_id,
            'order_url' => URL::temporarySignedRoute(
                'orders.show',
                now()->addHours(12),
                ['order' => $order],
            ),
        ], 201);
    }

    public function show(Order $order): View
    {
        return view('orders.status', [
            'order' => $order->load('items'),
            'statusUrl' => URL::temporarySignedRoute(
                'orders.status',
                now()->addHours(12),
                ['order' => $order],
            ),
            'receiptUrl' => $this->receiptUrl($order),
            'printUrl' => $this->printReceiptUrl($order),
        ]);
    }

    public function status(Order $order): JsonResponse
    {
        return response()->json([
            'status' => $order->status,
            'payment_method' => $order->payment_method,
            'receipt_url' => $this->receiptUrl($order),
            'print_url' => $this->printReceiptUrl($order),
        ]);
    }

    public function receipt(Order $order): StreamedResponse
    {
        abort_unless($order->status === 'paid' && $order->receipt_path !== null, 404);

        return Storage::disk('local')->download(
            $order->receipt_path,
            'struk-'.$order->order_id.'.pdf',
        );
    }

    public function printReceipt(Order $order, ReceiptPrinter $receiptPrinter): JsonResponse|StreamedResponse
    {
        abort_unless($order->status === 'paid', 404);

        $receiptPath = $receiptPrinter->print($order);
        $order->update([
            'receipt_path' => $receiptPath,
            'receipt_completed_at' => now(),
        ]);

        if (config('receipt.driver') === 'pdf') {
            return Storage::disk('local')->download(
                $receiptPath,
                'struk-'.$order->order_id.'.pdf',
            );
        }

        return response()->json(['message' => 'Struk berhasil dicetak.']);
    }

    private function receiptUrl(Order $order): ?string
    {
        if ($order->status !== 'paid' || $order->receipt_path === null) {
            return null;
        }

        return URL::temporarySignedRoute(
            'orders.receipt',
            now()->addHours(12),
            ['order' => $order],
        );
    }

    private function printReceiptUrl(Order $order): ?string
    {
        if ($order->status !== 'paid') {
            return null;
        }

        return URL::temporarySignedRoute(
            'orders.print-receipt',
            now()->addHours(12),
            ['order' => $order],
        );
    }
}
