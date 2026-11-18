<?php

namespace App\Services;

use Midtrans\Config;
use Midtrans\Snap;
use RuntimeException;

class MidtransGateway
{
    /**
     * @param  array<string, mixed>  $params
     */
    public function createSnapToken(array $params): string
    {
        $serverKey = config('midtrans.server_key');

        if (! is_string($serverKey) || $serverKey === '') {
            throw new RuntimeException('MIDTRANS_SERVER_KEY belum dikonfigurasi.');
        }

        Config::$serverKey = $serverKey;
        Config::$isProduction = (bool) config('midtrans.is_production');
        Config::$isSanitized = (bool) config('midtrans.is_sanitized');
        Config::$is3ds = (bool) config('midtrans.is_3ds');

        return Snap::getSnapToken($params);
    }

    public function hasServerKey(): bool
    {
        return is_string(config('midtrans.server_key'))
            && config('midtrans.server_key') !== '';
    }

    public function hasValidSignature(
        string $orderId,
        string $statusCode,
        string $grossAmount,
        string $signature,
    ): bool {
        $serverKey = config('midtrans.server_key');

        if (! is_string($serverKey) || $serverKey === '') {
            return false;
        }

        $expectedSignature = hash('sha512', $orderId.$statusCode.$grossAmount.$serverKey);

        return hash_equals($expectedSignature, $signature);
    }
}
