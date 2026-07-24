<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Throwable;

class BusinessConsistencyAudit extends Command
{
    protected $signature = 'project:audit-business
        {--order= : Chỉ kiểm tra một mã đơn hàng}
        {--limit=500 : Số bản ghi tối đa cho mỗi nhóm kiểm tra}
        {--strict : Trả exit code lỗi khi có cảnh báo}
        {--exclude-demo : Bỏ qua dữ liệu demo có mã chứa DEMO}
        {--since= : Chỉ kiểm tra dữ liệu phát sinh từ thời điểm này (Y-m-d hoặc Y-m-d H:i:s)}';

    protected $description = 'Kiểm tra tính nhất quán dữ liệu Order, Inventory, Coupon, Payment, Shipment, Return, Refund, Loyalty và Notification';

    /** @var array<int, string> */
    private array $errors = [];

    /** @var array<int, string> */
    private array $warnings = [];

    /** @var array<int, string> */
    private array $passes = [];

    public function handle(): int
    {
        $this->newLine();
        $this->info('COSMETICSHOP - KIỂM TRA NHẤT QUÁN NGHIỆP VỤ');
        $this->newLine();

        try {
            $this->checkRequiredTables();

            if ($this->errors !== []) {
                return $this->renderResult();
            }

            $this->checkOrderAmountsAndQuantities();
            $this->checkOrderPaymentConsistency();
            $this->checkShipmentConsistency();
            $this->checkInventoryConsistency();
            $this->checkCouponConsistency();
            $this->checkRefundConsistency();
            $this->checkReturnConsistency();
            $this->checkLoyaltyConsistency();
            $this->checkNotificationCoverage();
        } catch (Throwable $exception) {
            $this->errors[] = 'Command dừng vì exception: '.$exception->getMessage();
        }

        return $this->renderResult();
    }

    private function checkRequiredTables(): void
    {
        $required = [
            'orders',
            'order_items',
            'payments',
            'inventories',
            'inventory_transactions',
            'coupon_usages',
            'shipments',
            'return_requests',
            'refunds',
            'loyalty_accounts',
            'loyalty_transactions',
            'notifications',
        ];

        $missing = array_values(array_filter(
            $required,
            static fn (string $table): bool => ! Schema::hasTable($table)
        ));

        if ($missing !== []) {
            $this->errors[] = 'Thiếu bảng: '.implode(', ', $missing).'.';
            return;
        }

        $this->passes[] = 'Đã tìm thấy đầy đủ các bảng nghiệp vụ cần kiểm tra.';
    }

    private function orderQuery()
    {
        $query = DB::table('orders')->whereNull('deleted_at');

        $orderCode = trim((string) $this->option('order'));
        if ($orderCode !== '') {
            $query->where('order_code', $orderCode);
        }

        if ((bool) $this->option('exclude-demo')) {
            $query->where('order_code', 'not like', '%DEMO%');
        }

        $since = trim((string) $this->option('since'));
        if ($since !== '') {
            $query->where('created_at', '>=', $since);
        }

        return $query;
    }

    private function limit(): int
    {
        return max(1, min(5000, (int) $this->option('limit')));
    }

    private function checkOrderAmountsAndQuantities(): void
    {
        $orders = $this->orderQuery()
            ->orderByDesc('id')
            ->limit($this->limit())
            ->get();

        foreach ($orders as $order) {
            $expectedTotal = (float) $order->subtotal
                - (float) $order->product_discount
                - (float) $order->coupon_discount
                + (float) $order->shipping_fee
                + (float) $order->tax_amount
                - (float) $order->point_discount;

            if (abs($expectedTotal - (float) $order->total_amount) > 0.01) {
                $this->errors[] = sprintf(
                    'Đơn %s lệch tổng tiền: DB=%s, tính lại=%s.',
                    $order->order_code,
                    $this->money($order->total_amount),
                    $this->money($expectedTotal)
                );
            }

            $itemQuantity = (int) DB::table('order_items')
                ->where('order_id', $order->id)
                ->sum('quantity');

            if ($itemQuantity !== (int) $order->total_quantity) {
                $this->errors[] = sprintf(
                    'Đơn %s lệch tổng số lượng: orders.total_quantity=%d, items=%d.',
                    $order->order_code,
                    $order->total_quantity,
                    $itemQuantity
                );
            }

            if ($order->order_status === 'completed' && $order->completed_at === null) {
                $this->warnings[] = "Đơn {$order->order_code} đã completed nhưng completed_at đang null.";
            }

            if ($order->order_status === 'cancelled' && $order->cancelled_at === null) {
                $this->warnings[] = "Đơn {$order->order_code} đã cancelled nhưng cancelled_at đang null.";
            }
        }

        $this->passes[] = 'Đã kiểm tra tổng tiền, tổng số lượng và mốc thời gian của '.$orders->count().' đơn hàng.';
    }

    private function checkOrderPaymentConsistency(): void
    {
        $orders = $this->orderQuery()
            ->orderByDesc('id')
            ->limit($this->limit())
            ->get();

        foreach ($orders as $order) {
            $payments = DB::table('payments')
                ->where('order_id', $order->id)
                ->orderByDesc('id')
                ->get();

            if ($payments->isEmpty()) {
                $this->errors[] = "Đơn {$order->order_code} không có bản ghi payment.";
                continue;
            }

            $latest = $payments->first();
            $paid = $payments->firstWhere('status', 'paid');

            if ($order->payment_status === 'paid' && $paid === null) {
                $this->errors[] = "Đơn {$order->order_code} payment_status=paid nhưng không có payment paid.";
            }

            if ($paid !== null && $order->payment_status !== 'paid') {
                $this->errors[] = "Đơn {$order->order_code} có payment paid nhưng orders.payment_status={$order->payment_status}.";
            }

            if ($order->order_status === 'cancelled'
                && ! in_array($latest->status, ['cancelled', 'refunded', 'partially_refunded'], true)) {
                $this->warnings[] = "Đơn {$order->order_code} đã hủy nhưng payment gần nhất={$latest->status}.";
            }

            if (abs((float) $latest->amount - (float) $order->total_amount) > 0.01) {
                $this->warnings[] = sprintf(
                    'Đơn %s có payment amount=%s khác total_amount=%s.',
                    $order->order_code,
                    $this->money($latest->amount),
                    $this->money($order->total_amount)
                );
            }
        }

        $this->passes[] = 'Đã đối chiếu trạng thái và số tiền giữa orders và payments.';
    }

    private function checkShipmentConsistency(): void
    {
        $shipments = DB::table('shipments as s')
            ->join('orders as o', 'o.id', '=', 's.order_id')
            ->whereNull('o.deleted_at')
            ->when(
                trim((string) $this->option('order')) !== '',
                fn ($query) => $query->where('o.order_code', trim((string) $this->option('order')))
            )
            ->when(
                (bool) $this->option('exclude-demo'),
                fn ($query) => $query->where('o.order_code', 'not like', '%DEMO%')
            )
            ->when(
                trim((string) $this->option('since')) !== '',
                fn ($query) => $query->where('o.created_at', '>=', trim((string) $this->option('since')))
            )
            ->select([
                's.id', 's.shipment_code', 's.status as shipment_status',
                's.delivered_at', 's.cod_amount', 's.shipping_fee',
                'o.order_code', 'o.order_status', 'o.shipping_status',
                'o.payment_status', 'o.payment_method', 'o.total_amount',
                'o.shipping_fee as order_shipping_fee',
            ])
            ->orderByDesc('s.id')
            ->limit($this->limit())
            ->get();

        foreach ($shipments as $shipment) {
            if ($shipment->shipment_status === 'delivered') {
                if ($shipment->shipping_status !== 'delivered') {
                    $this->errors[] = "Shipment {$shipment->shipment_code} delivered nhưng order {$shipment->order_code} shipping_status={$shipment->shipping_status}.";
                }

                if ($shipment->order_status !== 'completed') {
                    $this->errors[] = "Shipment {$shipment->shipment_code} delivered nhưng order {$shipment->order_code} chưa completed.";
                }

                if ($shipment->delivered_at === null) {
                    $this->warnings[] = "Shipment {$shipment->shipment_code} delivered nhưng delivered_at null.";
                }

                if ($shipment->payment_method === 'cod'
                    && ! in_array($shipment->payment_status, ['paid', 'partially_refunded', 'refunded'], true)) {
                    $this->errors[] = "Đơn COD {$shipment->order_code} đã giao nhưng payment_status={$shipment->payment_status}.";
                }
            }

            if (abs((float) $shipment->shipping_fee - (float) $shipment->order_shipping_fee) > 0.01) {
                $this->warnings[] = "Shipment {$shipment->shipment_code} có shipping_fee khác đơn {$shipment->order_code}.";
            }

            if ($shipment->payment_method === 'cod'
                && abs((float) $shipment->cod_amount - (float) $shipment->total_amount) > 0.01) {
                $this->warnings[] = "Shipment {$shipment->shipment_code} có cod_amount khác total_amount của đơn.";
            }
        }

        $this->passes[] = 'Đã kiểm tra đồng bộ shipment, order và COD.';
    }

    private function checkInventoryConsistency(): void
    {
        $invalid = DB::table('inventories')
            ->where(function ($query): void {
                $query->where('quantity', '<', 0)
                    ->orWhere('reserved_quantity', '<', 0)
                    ->orWhere('sold_quantity', '<', 0)
                    ->orWhereColumn('reserved_quantity', '>', 'quantity');
            })
            ->orderBy('id')
            ->limit($this->limit())
            ->get();

        foreach ($invalid as $inventory) {
            $this->errors[] = sprintf(
                'Inventory #%d (warehouse=%d, sku=%d) không hợp lệ: quantity=%d, reserved=%d, sold=%d.',
                $inventory->id,
                $inventory->warehouse_id,
                $inventory->sku_id,
                $inventory->quantity,
                $inventory->reserved_quantity,
                $inventory->sold_quantity
            );
        }

        // Không thể quy reserved_quantity tổng của một SKU cho riêng một đơn đã hủy,
        // vì cùng SKU có thể đang được giữ cho nhiều đơn còn hoạt động. Việc hoàn giữ
        // phải được đối chiếu theo inventory_transactions ở bài kiểm tra chuyên sâu.

        $this->passes[] = 'Đã kiểm tra tồn âm, giữ tồn âm và reserved vượt quantity.';
    }

    private function checkCouponConsistency(): void
    {
        $duplicates = DB::table('coupon_usages')
            ->select('coupon_id', 'order_id', DB::raw('COUNT(*) as total'))
            ->groupBy('coupon_id', 'order_id')
            ->havingRaw('COUNT(*) > 1')
            ->limit($this->limit())
            ->get();

        foreach ($duplicates as $duplicate) {
            $this->errors[] = "Coupon #{$duplicate->coupon_id} bị ghi {$duplicate->total} lần cho order #{$duplicate->order_id}.";
        }

        $usages = DB::table('coupon_usages as cu')
            ->join('orders as o', 'o.id', '=', 'cu.order_id')
            ->whereNull('o.deleted_at')
            ->select('cu.*', 'o.order_code', 'o.coupon_id as order_coupon_id', 'o.coupon_discount', 'o.order_status')
            ->orderByDesc('cu.id')
            ->limit($this->limit())
            ->get();

        foreach ($usages as $usage) {
            if ((int) $usage->coupon_id !== (int) $usage->order_coupon_id) {
                $this->errors[] = "Coupon usage #{$usage->id} không khớp coupon_id của đơn {$usage->order_code}.";
            }

            if (abs((float) $usage->discount_amount - (float) $usage->coupon_discount) > 0.01) {
                $this->warnings[] = "Coupon usage của đơn {$usage->order_code} lệch coupon_discount.";
            }

            if ($usage->order_status === 'cancelled') {
                $this->warnings[] = "Đơn {$usage->order_code} đã hủy nhưng vẫn còn coupon_usage #{$usage->id}.";
            }
        }

        $this->passes[] = 'Đã kiểm tra coupon usage trùng, coupon của đơn và số tiền giảm.';
    }

    private function checkRefundConsistency(): void
    {
        $payments = DB::table('payments')
            ->orderByDesc('id')
            ->limit($this->limit())
            ->get();

        foreach ($payments as $payment) {
            $completedAmount = (float) DB::table('refunds')
                ->where('payment_id', $payment->id)
                ->where('status', 'completed')
                ->sum('amount');

            if ($completedAmount - (float) $payment->amount > 0.01) {
                $this->errors[] = sprintf(
                    'Payment %s bị refund vượt tiền: payment=%s, completed_refund=%s.',
                    $payment->payment_code,
                    $this->money($payment->amount),
                    $this->money($completedAmount)
                );
            }

            $expected = null;
            if ($completedAmount > 0 && $completedAmount + 0.01 < (float) $payment->amount) {
                $expected = 'partially_refunded';
            } elseif ($completedAmount > 0 && $completedAmount + 0.01 >= (float) $payment->amount) {
                $expected = 'refunded';
            }

            if ($expected !== null && $payment->status !== $expected) {
                $this->errors[] = "Payment {$payment->payment_code} nên có status={$expected}, hiện tại={$payment->status}.";
            }
        }

        $this->passes[] = 'Đã kiểm tra tổng refund không vượt payment và trạng thái hoàn tiền.';
    }

    private function checkReturnConsistency(): void
    {
        $returns = DB::table('return_requests as rr')
            ->join('orders as o', 'o.id', '=', 'rr.order_id')
            ->whereNull('o.deleted_at')
            ->when(
                trim((string) $this->option('order')) !== '',
                fn ($query) => $query->where('o.order_code', trim((string) $this->option('order')))
            )
            ->when(
                (bool) $this->option('exclude-demo'),
                fn ($query) => $query->where('o.order_code', 'not like', '%DEMO%')
            )
            ->when(
                trim((string) $this->option('since')) !== '',
                fn ($query) => $query->where('rr.created_at', '>=', trim((string) $this->option('since')))
            )
            ->select('rr.*', 'o.order_code')
            ->orderByDesc('rr.id')
            ->limit($this->limit())
            ->get();

        foreach ($returns as $return) {
            $completedRefund = (float) DB::table('refunds')
                ->where('return_request_id', $return->id)
                ->where('status', 'completed')
                ->sum('amount');

            $approvedAmount = (float) ($return->approved_amount ?: $return->requested_amount);

            if ($return->status === 'completed') {
                if ($return->completed_at === null) {
                    $this->warnings[] = "Return {$return->return_code} completed nhưng completed_at null.";
                }

                if ($approvedAmount > 0 && $completedRefund + 0.01 < $approvedAmount) {
                    $this->errors[] = sprintf(
                        'Return %s completed nhưng refund mới %s/%s.',
                        $return->return_code,
                        $this->money($completedRefund),
                        $this->money($approvedAmount)
                    );
                }
            }
        }

        $this->passes[] = 'Đã kiểm tra return completed, completed_at và refund tương ứng.';
    }

    private function checkLoyaltyConsistency(): void
    {
        $duplicateEarns = DB::table('loyalty_transactions')
            ->select('reference_type', 'reference_id', DB::raw('COUNT(*) as total'))
            ->where('type', 'earn')
            ->where('status', 'completed')
            ->whereNotNull('reference_type')
            ->whereNotNull('reference_id')
            ->groupBy('reference_type', 'reference_id')
            ->havingRaw('COUNT(*) > 1')
            ->limit($this->limit())
            ->get();

        foreach ($duplicateEarns as $duplicate) {
            $this->errors[] = "Loyalty earn bị trùng {$duplicate->total} lần cho {$duplicate->reference_type} #{$duplicate->reference_id}.";
        }

        $invalidTransactions = DB::table('loyalty_transactions')
            ->where(function ($query): void {
                $query->where('balance_before', '<', 0)
                    ->orWhere('balance_after', '<', 0);
            })
            ->limit($this->limit())
            ->get();

        foreach ($invalidTransactions as $transaction) {
            $this->errors[] = "Loyalty transaction #{$transaction->id} có balance âm.";
        }

        $completedOrders = $this->orderQuery()
            ->where('order_status', 'completed')
            ->whereNotNull('user_id')
            ->orderByDesc('id')
            ->limit($this->limit())
            ->get();

        foreach ($completedOrders as $order) {
            $hasEarn = DB::table('loyalty_transactions')
                ->where('order_id', $order->id)
                ->where('type', 'earn')
                ->where('status', 'completed')
                ->exists();

            if (! $hasEarn) {
                $this->warnings[] = "Đơn completed {$order->order_code} chưa có loyalty earn completed.";
            }
        }

        $this->passes[] = 'Đã kiểm tra loyalty earn trùng, balance âm và đơn completed chưa cộng điểm.';
    }

    private function checkNotificationCoverage(): void
    {
        $recentOrders = $this->orderQuery()
            ->orderByDesc('id')
            ->limit(min(100, $this->limit()))
            ->get();

        foreach ($recentOrders as $order) {
            $notificationQuery = DB::table('notifications')
                ->where(function ($query) use ($order): void {
                    $query->where('data', 'like', '%"order_id":'.$order->id.'%')
                        ->orWhere('data', 'like', '%"order_code":"'.$order->order_code.'"%');
                });

            if ($order->order_status === 'completed'
                && ! (clone $notificationQuery)->where('type', 'like', '%completed%')->exists()) {
                $this->warnings[] = "Đơn completed {$order->order_code} chưa tìm thấy notification hoàn thành.";
            }

            if ($order->order_status === 'cancelled'
                && ! (clone $notificationQuery)->where('type', 'like', '%cancel%')->exists()) {
                $this->warnings[] = "Đơn cancelled {$order->order_code} chưa tìm thấy notification hủy.";
            }
        }

        $this->passes[] = 'Đã rà notification của các đơn gần nhất (kiểm tra theo JSON data/type).';
    }

    private function renderResult(): int
    {
        foreach ($this->passes as $pass) {
            $this->line('<fg=green>✓</> '.$pass);
        }

        if ($this->warnings !== []) {
            $this->newLine();
            $this->warn('CẢNH BÁO ('.count($this->warnings).')');
            foreach ($this->warnings as $index => $warning) {
                $this->line(($index + 1).'. '.$warning);
            }
        }

        if ($this->errors !== []) {
            $this->newLine();
            $this->error('LỖI NGHIỆP VỤ ('.count($this->errors).')');
            foreach ($this->errors as $index => $error) {
                $this->line(($index + 1).'. '.$error);
            }
        }

        $this->newLine();
        if ($this->errors === [] && $this->warnings === []) {
            $this->info('Không phát hiện dữ liệu nghiệp vụ bất nhất.');
            return self::SUCCESS;
        }

        if ($this->errors !== []) {
            $this->error('Kiểm tra thất bại. Hãy sửa các lỗi nghiệp vụ trước khi tiếp tục.');
            return self::FAILURE;
        }

        $this->warn('Không có lỗi nghiêm trọng, nhưng còn cảnh báo cần đối chiếu.');

        return $this->option('strict') ? self::FAILURE : self::SUCCESS;
    }

    private function money(float|int|string|null $amount): string
    {
        return number_format((float) $amount, 0, ',', '.').'đ';
    }
}
