<?php

namespace App\Http\Controllers\Client;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;
use Throwable;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class CartController extends Controller
{
    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'sku_id' => [
                'nullable',
                'integer',
                'exists:product_skus,id',
                'required_without:product_id',
            ],
            'product_id' => [
                'nullable',
                'integer',
                'exists:products,id',
                'required_without:sku_id',
            ],
            'quantity' => [
                'required',
                'integer',
                'min:1',
                'max:99',
            ],
        ], [
            'sku_id.required_without' =>
                'Không xác định được phiên bản sản phẩm.',
            'product_id.required_without' =>
                'Không xác định được sản phẩm.',
            'quantity.required' =>
                'Vui lòng nhập số lượng.',
            'quantity.integer' =>
                'Số lượng phải là số nguyên.',
            'quantity.min' =>
                'Số lượng tối thiểu là 1.',
            'quantity.max' =>
                'Mỗi lần chỉ được thêm tối đa 99 sản phẩm.',
        ]);

        try {
            return DB::transaction(function () use (
                $request,
                $validated
            ): JsonResponse {
                $sku = $this->findSku(
                    isset($validated['sku_id'])
                        ? (int) $validated['sku_id']
                        : null,
                    isset($validated['product_id'])
                        ? (int) $validated['product_id']
                        : null
                );

                if (!$sku) {
                    throw ValidationException::withMessages([
                        'product' =>
                            'Sản phẩm hoặc phiên bản này không khả dụng.',
                    ]);
                }

                $availableQuantity = $this->availableQuantity(
                    (int) $sku->id
                );

                if ($availableQuantity <= 0) {
                    throw ValidationException::withMessages([
                        'quantity' =>
                            'Sản phẩm hiện đã hết hàng.',
                    ]);
                }

                $cartId = $this->getOrCreateActiveCartId(
                    $request
                );

                $currentItem = DB::table('cart_items')
                    ->where('cart_id', $cartId)
                    ->where('sku_id', $sku->id)
                    ->lockForUpdate()
                    ->first();

                $requestedQuantity = (int) $validated['quantity'];

                $newQuantity = $currentItem
                    ? (int) $currentItem->quantity
                        + $requestedQuantity
                    : $requestedQuantity;

                if ($newQuantity > $availableQuantity) {
                    throw ValidationException::withMessages([
                        'quantity' =>
                            "Kho chỉ còn {$availableQuantity} sản phẩm.",
                    ]);
                }

                $discountAmount = $this->getUnitDiscount(
                    (int) $sku->product_id,
                    (float) $sku->price
                );

                if ($currentItem) {
                    DB::table('cart_items')
                        ->where('id', $currentItem->id)
                        ->update([
                            'quantity' => $newQuantity,
                            'unit_price' => $sku->price,
                            'discount_amount' => $discountAmount,
                            'updated_at' => now(),
                        ]);
                } else {
                    DB::table('cart_items')->insert([
                        'cart_id' => $cartId,
                        'sku_id' => $sku->id,
                        'quantity' => $newQuantity,
                        'unit_price' => $sku->price,
                        'discount_amount' => $discountAmount,
                        'created_at' => now(),
                        'updated_at' => now(),
                    ]);
                }

                $cartCount = (int) DB::table('cart_items')
                    ->where('cart_id', $cartId)
                    ->sum('quantity');

                return response()->json([
                    'success' => true,
                    'message' =>
                        'Đã thêm sản phẩm vào giỏ hàng.',
                    'cart_count' => $cartCount,
                ]);
            });
        } catch (ValidationException $exception) {
            throw $exception;
        } catch (Throwable $exception) {
            report($exception);

            return response()->json([
                'success' => false,
                'message' =>
                    'Không thể thêm sản phẩm vào giỏ hàng. Vui lòng thử lại.',
            ], 500);
        }
    }

    private function findSku(
        ?int $skuId,
        ?int $productId
    ): ?object {
        $query = DB::table('product_skus as ps')
            ->join(
                'products as p',
                'ps.product_id',
                '=',
                'p.id'
            )
            ->where('ps.status', true)
            ->where('p.status', true)
            ->whereNull('p.deleted_at')
            ->select([
                'ps.id',
                'ps.product_id',
                'ps.price',
                'ps.sku_code',
            ]);

        if ($skuId !== null) {
            return $query
                ->where('ps.id', $skuId)
                ->first();
        }

        return $query
            ->where('ps.product_id', $productId)
            ->orderBy('ps.price')
            ->orderBy('ps.id')
            ->first();
    }

    private function availableQuantity(int $skuId): int
    {
        return max(
            0,
            (int) DB::table('inventories')
                ->where('sku_id', $skuId)
                ->selectRaw(
                    'COALESCE(
                        SUM(quantity - reserved_quantity),
                        0
                    ) AS available_quantity'
                )
                ->value('available_quantity')
        );
    }

    private function getOrCreateActiveCartId(
        Request $request
    ): int {
        $userId = auth()->id();
        $sessionId = $request->session()->getId();

        $cartQuery = DB::table('carts')
            ->where('status', 'active');

        if ($userId) {
            $cartQuery->where('user_id', $userId);
        } else {
            $cartQuery
                ->whereNull('user_id')
                ->where('session_id', $sessionId);
        }

        $cart = $cartQuery
            ->lockForUpdate()
            ->first();

        if ($cart) {
            DB::table('carts')
                ->where('id', $cart->id)
                ->update([
                    'expires_at' => now()->addDays(30),
                    'updated_at' => now(),
                ]);

            return (int) $cart->id;
        }

        return (int) DB::table('carts')->insertGetId([
            'user_id' => $userId,
            'session_id' => $userId ? null : $sessionId,
            'status' => 'active',
            'expires_at' => now()->addDays(30),
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }

    private function getUnitDiscount(
        int $productId,
        float $originalPrice
    ): float {
        $discount = DB::table('product_discounts as pd')
            ->join(
                'discount_campaigns as dc',
                'pd.campaign_id',
                '=',
                'dc.id'
            )
            ->where('pd.product_id', $productId)
            ->where('dc.status', true)
            ->where('dc.start_date', '<=', now())
            ->where('dc.end_date', '>=', now())
            ->orderByDesc('dc.is_flash_sale')
            ->orderBy('dc.end_date')
            ->select([
                'pd.discount_percent',
                'pd.discount_amount',
                'pd.limit_quantity',
                'pd.sold_quantity',
            ])
            ->first();

        if (!$discount) {
            return 0;
        }

        if (
            $discount->limit_quantity !== null
            && (int) $discount->sold_quantity
                >= (int) $discount->limit_quantity
        ) {
            return 0;
        }

        if ($discount->discount_percent !== null) {
            return round(
                $originalPrice
                * ((float) $discount->discount_percent / 100),
                2
            );
        }

        if ($discount->discount_amount !== null) {
            return min(
                $originalPrice,
                (float) $discount->discount_amount
            );
        }

        return 0;
    }

public function index(Request $request): View
{
    $cart = $this->findActiveCart($request);

    $items = collect();
    $subtotal = 0;
    $discountTotal = 0;
    $grandTotal = 0;

    if ($cart) {
        $items = DB::table('cart_items as ci')
            ->join(
                'product_skus as ps',
                'ci.sku_id',
                '=',
                'ps.id'
            )
            ->join(
                'products as p',
                'ps.product_id',
                '=',
                'p.id'
            )
            ->leftJoin(
                'brands as b',
                'p.brand_id',
                '=',
                'b.id'
            )
            ->leftJoin(
                'product_variants as pv',
                'ps.variant_id',
                '=',
                'pv.id'
            )
            ->where('ci.cart_id', $cart->id)
            ->select([
                'ci.id',
                'ci.quantity',
                'ci.unit_price',
                'ci.discount_amount',
                'ps.id as sku_id',
                'ps.sku_code',
                'ps.weight',
                'p.id as product_id',
                'p.name',
                'p.slug',
                'b.name as brand_name',
                'pv.name as variant_name',
            ])
            ->selectSub(function ($query): void {
                $query
                    ->from('product_images')
                    ->select('image_path')
                    ->whereColumn(
                        'product_images.product_id',
                        'p.id'
                    )
                    ->orderByDesc('is_thumbnail')
                    ->orderBy('sort_order')
                    ->limit(1);
            }, 'image_path')
            ->selectSub(function ($query): void {
                $query
                    ->from('inventories')
                    ->selectRaw(
                        'COALESCE(
                            SUM(quantity - reserved_quantity),
                            0
                        )'
                    )
                    ->whereColumn(
                        'inventories.sku_id',
                        'ps.id'
                    );
            }, 'available_quantity')
            ->orderByDesc('ci.id')
            ->get()
            ->map(function ($item) {
                $item->unit_price = (float) $item->unit_price;
                $item->discount_amount =
                    (float) $item->discount_amount;

                $item->final_unit_price = max(
                    0,
                    $item->unit_price - $item->discount_amount
                );

                $item->line_subtotal =
                    $item->unit_price * $item->quantity;

                $item->line_discount =
                    $item->discount_amount * $item->quantity;

                $item->line_total =
                    $item->final_unit_price * $item->quantity;

                return $item;
            });

        $subtotal = (float) $items->sum('line_subtotal');
        $discountTotal = (float) $items->sum('line_discount');
        $grandTotal = (float) $items->sum('line_total');
    }

    return view('client.cart.index', compact(
        'cart',
        'items',
        'subtotal',
        'discountTotal',
        'grandTotal'
    ));
}

public function update(
    Request $request,
    int $itemId
): JsonResponse {
    $validated = $request->validate([
        'quantity' => [
            'required',
            'integer',
            'min:1',
            'max:99',
        ],
    ]);

    $cart = $this->findActiveCart($request);

    if (!$cart) {
        return response()->json([
            'success' => false,
            'message' => 'Không tìm thấy giỏ hàng.',
        ], 404);
    }

    $item = DB::table('cart_items')
        ->where('id', $itemId)
        ->where('cart_id', $cart->id)
        ->first();

    if (!$item) {
        return response()->json([
            'success' => false,
            'message' => 'Sản phẩm không còn trong giỏ hàng.',
        ], 404);
    }

    $availableQuantity = $this->availableQuantity(
        (int) $item->sku_id
    );

    $quantity = (int) $validated['quantity'];

    if ($quantity > $availableQuantity) {
        return response()->json([
            'success' => false,
            'message' =>
                "Kho chỉ còn {$availableQuantity} sản phẩm.",
        ], 422);
    }

    DB::table('cart_items')
        ->where('id', $itemId)
        ->update([
            'quantity' => $quantity,
            'updated_at' => now(),
        ]);

    $updatedItem = DB::table('cart_items')
    ->where('id', $itemId)
    ->first();

$lineTotal = max(
    0,
    (float) $updatedItem->unit_price
    - (float) $updatedItem->discount_amount
) * (int) $updatedItem->quantity;

return response()->json([
    'success' => true,
    'message' => 'Đã cập nhật số lượng.',
    'item_id' => $itemId,
    'quantity' => (int) $updatedItem->quantity,
    'line_total' => $lineTotal,
    ...$this->cartTotals((int) $cart->id),
]);
}

public function destroy(
    Request $request,
    int $itemId
): JsonResponse {
    $cart = $this->findActiveCart($request);

    if (!$cart) {
        return response()->json([
            'success' => false,
            'message' => 'Không tìm thấy giỏ hàng.',
        ], 404);
    }

    DB::table('cart_items')
        ->where('id', $itemId)
        ->where('cart_id', $cart->id)
        ->delete();

    return response()->json([
        'success' => true,
        'message' => 'Đã xóa sản phẩm khỏi giỏ hàng.',
        ...$this->cartTotals((int) $cart->id),
    ]);
}
private function findActiveCart(Request $request): ?object
{
    $query = DB::table('carts')
        ->where('status', 'active');

    if (auth()->check()) {
        $query->where('user_id', auth()->id());
    } else {
        $query
            ->whereNull('user_id')
            ->where(
                'session_id',
                $request->session()->getId()
            );
    }

    return $query
        ->latest('id')
        ->first();
}

private function cartTotals(int $cartId): array
{
    $rows = DB::table('cart_items')
        ->where('cart_id', $cartId)
        ->get([
            'quantity',
            'unit_price',
            'discount_amount',
        ]);

    $subtotal = 0;
    $discountTotal = 0;
    $grandTotal = 0;
    $cartCount = 0;

    foreach ($rows as $row) {
        $quantity = (int) $row->quantity;
        $unitPrice = (float) $row->unit_price;
        $discount = (float) $row->discount_amount;

        $subtotal += $unitPrice * $quantity;
        $discountTotal += $discount * $quantity;
        $grandTotal += max(
            0,
            $unitPrice - $discount
        ) * $quantity;
        $cartCount += $quantity;
    }

    return [
        'subtotal' => $subtotal,
        'discount_total' => $discountTotal,
        'grand_total' => $grandTotal,
        'cart_count' => $cartCount,
    ];
}
}
