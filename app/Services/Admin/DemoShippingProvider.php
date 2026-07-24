<?php

namespace App\Services\Admin;

use App\Models\Order;
use App\Models\Shipment;
use App\Models\ShippingMethod;
use Illuminate\Support\Str;

class DemoShippingProvider
{
    /**
     * Chuẩn bị toàn bộ dữ liệu kiện hàng dựa trên thông tin khách đã chọn.
     */
    public function prepare(Order $order): array
    {
        $order->loadMissing([
            'items.sku:id,weight',
            'shippingAddress',
        ]);

        $shippingMethod = $this->resolveShippingMethod($order);
        $providerKey = $this->normalizeProvider(
            $shippingMethod?->provider
                ?: config('shipping.demo.default_provider', 'ghn')
        );
        $provider = config("shipping.providers.{$providerKey}")
            ?: config('shipping.providers.internal');

        $totalWeight = (int) $order->items->sum(function ($item): int {
            $unitWeight = (int) ($item->sku?->weight ?: 0);

            if ($unitWeight < 1) {
                $unitWeight = (int) config(
                    'shipping.demo.default_weight_per_item',
                    250
                );
            }

            return $unitWeight * max(1, (int) $item->quantity);
        });

        $dimensions = config('shipping.demo.default_dimensions', [
            'length' => 25,
            'width' => 18,
            'height' => 12,
        ]);

        $estimatedDays = max(
            1,
            (int) (
                $shippingMethod?->estimated_days_max
                ?: $shippingMethod?->estimated_days_min
                ?: $this->estimateDaysFromAddress($order)
            )
        );

        $trackingCode = $this->generateTrackingCode(
            (string) ($provider['tracking_prefix'] ?? 'CS')
        );

        return [
            'shipping_method_id' => $shippingMethod?->id,
            'carrier_name' => (string) ($provider['name'] ?? 'Giao hàng nội bộ'),
            'service_name' => $shippingMethod?->name
                ?: (string) ($provider['default_service'] ?? 'Giao hàng tiêu chuẩn'),
            'tracking_code' => $trackingCode,
            'estimated_delivery_at' => now()->addDays($estimatedDays)->toDateString(),
            'weight' => max(1, $totalWeight),
            'length' => max(1, (int) ($dimensions['length'] ?? 25)),
            'width' => max(1, (int) ($dimensions['width'] ?? 18)),
            'height' => max(1, (int) ($dimensions['height'] ?? 12)),
            'provider_data' => [
                'mode' => 'demo',
                'provider' => $providerKey,
                'generated_at' => now()->toIso8601String(),
                'receiver' => [
                    'name' => $order->shippingAddress?->receiver_name
                        ?: $order->customer_name,
                    'phone' => $order->shippingAddress?->phone
                        ?: $order->customer_phone,
                    'address' => $order->shippingAddress?->formatted_address,
                ],
                'message' => 'Mã vận đơn mô phỏng. Có thể thay bằng mã thật khi kết nối API hãng vận chuyển.',
            ],
        ];
    }

    private function resolveShippingMethod(Order $order): ?ShippingMethod
    {
        if ($order->shipping_method_id) {
            $method = ShippingMethod::query()
                ->whereKey($order->shipping_method_id)
                ->where('status', true)
                ->first();

            if ($method) {
                return $method;
            }
        }

        // Đơn cũ chưa lưu shipping_method_id: ưu tiên phương thức có mức phí trùng.
        return ShippingMethod::query()
            ->where('status', true)
            ->orderByRaw('ABS(base_fee - ?) ASC', [(float) $order->shipping_fee])
            ->orderBy('sort_order')
            ->first();
    }

    private function normalizeProvider(string $provider): string
    {
        $normalized = Str::of($provider)->lower()->replace([' ', '-'], '_')->toString();

        return array_key_exists($normalized, config('shipping.providers', []))
            ? $normalized
            : 'internal';
    }

    private function estimateDaysFromAddress(Order $order): int
    {
        $province = Str::lower((string) $order->shippingAddress?->province);

        if (Str::contains($province, ['hà nội', 'ha noi'])) {
            return 2;
        }

        if (Str::contains($province, ['đà nẵng', 'da nang'])) {
            return 3;
        }

        if (Str::contains($province, ['hồ chí minh', 'ho chi minh', 'tp hcm'])) {
            return 4;
        }

        return 3;
    }

    private function generateTrackingCode(string $prefix): string
    {
        do {
            $code = strtoupper($prefix)
                . now()->format('ymdHis')
                . strtoupper(Str::random(4));
        } while (Shipment::query()->where('tracking_code', $code)->exists());

        return $code;
    }
}
