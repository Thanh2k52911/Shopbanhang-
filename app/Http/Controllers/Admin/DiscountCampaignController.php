<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Database\Query\Builder;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;
use Throwable;

class DiscountCampaignController extends Controller
{
    public function index(Request $request): View
    {
        $query = DB::table('discount_campaigns as dc')
            ->select([
                'dc.id',
                'dc.name',
                'dc.description',
                'dc.start_date',
                'dc.end_date',
                'dc.is_flash_sale',
                'dc.status',
                'dc.created_at',
                'dc.updated_at',
            ])
            ->selectSub(
                fn (Builder $builder) => $builder
                    ->from('product_discounts')
                    ->selectRaw('COUNT(*)')
                    ->whereColumn('product_discounts.campaign_id', 'dc.id'),
                'products_count'
            )
            ->selectSub(
                fn (Builder $builder) => $builder
                    ->from('product_discounts')
                    ->selectRaw('COALESCE(SUM(sold_quantity), 0)')
                    ->whereColumn('product_discounts.campaign_id', 'dc.id'),
                'sold_quantity'
            );

        $keyword = trim((string) $request->input('keyword'));

        if ($keyword !== '') {
            $query->where(function (Builder $builder) use ($keyword): void {
                $builder
                    ->where('dc.name', 'like', '%' . $keyword . '%')
                    ->orWhere('dc.description', 'like', '%' . $keyword . '%');
            });
        }

        if ($request->filled('status')) {
            $query->where('dc.status', (int) $request->input('status'));
        }

        if ($request->filled('flash_sale')) {
            $query->where(
                'dc.is_flash_sale',
                $request->input('flash_sale') === 'yes' ? 1 : 0
            );
        }

        match ($request->input('period')) {
            'active' => $query
                ->where('dc.status', 1)
                ->where('dc.start_date', '<=', now())
                ->where('dc.end_date', '>=', now()),

            'scheduled' => $query->where('dc.start_date', '>', now()),

            'ended' => $query->where('dc.end_date', '<', now()),

            default => null,
        };

        match ($request->input('sort')) {
            'oldest' => $query->orderBy('dc.id'),
            'name_asc' => $query->orderBy('dc.name'),
            'name_desc' => $query->orderByDesc('dc.name'),
            'start_desc' => $query->orderByDesc('dc.start_date'),
            'end_asc' => $query->orderBy('dc.end_date'),
            'products_desc' => $query->orderByDesc('products_count'),
            'sold_desc' => $query->orderByDesc('sold_quantity'),
            default => $query->orderByDesc('dc.id'),
        };

        $campaigns = $query
            ->paginate(20)
            ->withQueryString();

        $statistics = [
            'total' => DB::table('discount_campaigns')->count(),

            'active' => DB::table('discount_campaigns')
                ->where('status', 1)
                ->where('start_date', '<=', now())
                ->where('end_date', '>=', now())
                ->count(),

            'scheduled' => DB::table('discount_campaigns')
                ->where('start_date', '>', now())
                ->count(),

            'ended' => DB::table('discount_campaigns')
                ->where('end_date', '<', now())
                ->count(),

            'flash_sale' => DB::table('discount_campaigns')
                ->where('is_flash_sale', 1)
                ->count(),

            'sold_quantity' => (int) DB::table('product_discounts')
                ->sum('sold_quantity'),
        ];

        return view('admin.discount-campaigns.index', [
            'campaigns' => $campaigns,
            'statistics' => $statistics,
        ]);
    }

    public function create(): View
    {
        return view('admin.discount-campaigns.create', [
            'products' => $this->productOptions(),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate(
            $this->validationRules(),
            $this->validationMessages()
        );

        $this->validateBusinessRules($validated);

        DB::beginTransaction();

        try {
            $campaignId = DB::table('discount_campaigns')->insertGetId([
                'name' => trim($validated['name']),
                'description' => $this->nullableTrim(
                    $validated['description'] ?? null
                ),
                'start_date' => $validated['start_date'],
                'end_date' => $validated['end_date'],
                'is_flash_sale' => (int) ($validated['is_flash_sale'] ?? 0),
                'status' => (int) ($validated['status'] ?? 1),
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            $this->insertProductDiscounts(
                $campaignId,
                $validated['discounts']
            );

            DB::commit();

            return redirect()
                ->route('admin.discount-campaigns.show', $campaignId)
                ->with('success', 'Thêm chiến dịch giảm giá thành công.');
        } catch (Throwable $exception) {
            DB::rollBack();

            if ($exception instanceof ValidationException) {
                throw $exception;
            }

            report($exception);

            return back()
                ->withInput()
                ->with(
                    'error',
                    app()->isLocal()
                        ? 'Lỗi: ' . $exception->getMessage()
                        : 'Không thể thêm chiến dịch giảm giá.'
                );
        }
    }

    public function show(int $discountCampaign): View
    {
        $campaign = DB::table('discount_campaigns')
            ->where('id', $discountCampaign)
            ->first();

        abort_if(! $campaign, 404);

        $discounts = DB::table('product_discounts as pd')
            ->join('products as p', 'pd.product_id', '=', 'p.id')
            ->where('pd.campaign_id', $campaign->id)
            ->whereNull('p.deleted_at')
            ->orderBy('p.name')
            ->get([
                'pd.id',
                'pd.product_id',
                'pd.discount_percent',
                'pd.discount_amount',
                'pd.limit_quantity',
                'pd.sold_quantity',
                'pd.created_at',
                'pd.updated_at',
                'p.name as product_name',
                'p.slug as product_slug',
                'p.status as product_status',
            ]);

        $statistics = [
            'products_count' => $discounts->count(),
            'sold_quantity' => (int) $discounts->sum('sold_quantity'),
            'limited_products' => $discounts
                ->whereNotNull('limit_quantity')
                ->count(),
            'sold_out_products' => $discounts
                ->filter(
                    fn ($item) => $item->limit_quantity !== null
                        && (int) $item->sold_quantity
                            >= (int) $item->limit_quantity
                )
                ->count(),
        ];

        return view('admin.discount-campaigns.show', [
            'campaign' => $campaign,
            'discounts' => $discounts,
            'statistics' => $statistics,
        ]);
    }

    public function edit(int $discountCampaign): View
    {
        $campaign = DB::table('discount_campaigns')
            ->where('id', $discountCampaign)
            ->first();

        abort_if(! $campaign, 404);

        $discounts = DB::table('product_discounts')
            ->where('campaign_id', $campaign->id)
            ->orderBy('id')
            ->get();

        return view('admin.discount-campaigns.edit', [
            'campaign' => $campaign,
            'products' => $this->productOptions(),
            'discounts' => $discounts,
        ]);
    }

    public function update(
        Request $request,
        int $discountCampaign
    ): RedirectResponse {
        $campaign = DB::table('discount_campaigns')
            ->where('id', $discountCampaign)
            ->first();

        abort_if(! $campaign, 404);

        $validated = $request->validate(
            $this->validationRules(),
            $this->validationMessages()
        );

        $this->validateBusinessRules($validated);

        DB::beginTransaction();

        try {
            DB::table('discount_campaigns')
                ->where('id', $campaign->id)
                ->update([
                    'name' => trim($validated['name']),
                    'description' => $this->nullableTrim(
                        $validated['description'] ?? null
                    ),
                    'start_date' => $validated['start_date'],
                    'end_date' => $validated['end_date'],
                    'is_flash_sale' => (int) (
                        $validated['is_flash_sale'] ?? 0
                    ),
                    'status' => (int) ($validated['status'] ?? 1),
                    'updated_at' => now(),
                ]);

            $soldByProduct = DB::table('product_discounts')
                ->where('campaign_id', $campaign->id)
                ->pluck('sold_quantity', 'product_id');

            DB::table('product_discounts')
                ->where('campaign_id', $campaign->id)
                ->delete();

            $this->insertProductDiscounts(
                $campaign->id,
                $validated['discounts'],
                $soldByProduct->all()
            );

            DB::commit();

            return redirect()
                ->route(
                    'admin.discount-campaigns.show',
                    $campaign->id
                )
                ->with('success', 'Cập nhật chiến dịch thành công.');
        } catch (Throwable $exception) {
            DB::rollBack();

            if ($exception instanceof ValidationException) {
                throw $exception;
            }

            report($exception);

            return back()
                ->withInput()
                ->with(
                    'error',
                    app()->isLocal()
                        ? 'Lỗi: ' . $exception->getMessage()
                        : 'Không thể cập nhật chiến dịch.'
                );
        }
    }

    public function destroy(int $discountCampaign): RedirectResponse
    {
        $campaign = DB::table('discount_campaigns')
            ->where('id', $discountCampaign)
            ->first([
                'id',
                'name',
            ]);

        abort_if(! $campaign, 404);

        $hasSales = DB::table('product_discounts')
            ->where('campaign_id', $campaign->id)
            ->where('sold_quantity', '>', 0)
            ->exists();

        if ($hasSales) {
            return back()->with(
                'error',
                'Chiến dịch đã phát sinh lượt bán nên không thể xóa. Hãy chuyển trạng thái sang ngừng hoạt động.'
            );
        }

        DB::beginTransaction();

        try {
            DB::table('discount_campaigns')
                ->where('id', $campaign->id)
                ->delete();

            DB::commit();

            return redirect()
                ->route('admin.discount-campaigns.index')
                ->with(
                    'success',
                    'Đã xóa chiến dịch "' . $campaign->name . '".'
                );
        } catch (Throwable $exception) {
            DB::rollBack();

            report($exception);

            return back()->with(
                'error',
                app()->isLocal()
                    ? 'Lỗi xóa chiến dịch: '
                        . $exception->getMessage()
                    : 'Không thể xóa chiến dịch.'
            );
        }
    }

    private function validationRules(): array
    {
        return [
            'name' => [
                'required',
                'string',
                'max:255',
            ],

            'description' => [
                'nullable',
                'string',
            ],

            'start_date' => [
                'required',
                'date',
            ],

            'end_date' => [
                'required',
                'date',
            ],

            'is_flash_sale' => [
                'nullable',
                'boolean',
            ],

            'status' => [
                'nullable',
                'boolean',
            ],

            'discounts' => [
                'required',
                'array',
                'min:1',
            ],

            'discounts.*.product_id' => [
                'required',
                'integer',
                'distinct',
                Rule::exists('products', 'id')
                    ->whereNull('deleted_at'),
            ],

            'discounts.*.discount_percent' => [
                'nullable',
                'numeric',
                'min:0.01',
                'max:100',
            ],

            'discounts.*.discount_amount' => [
                'nullable',
                'numeric',
                'min:0.01',
                'max:99999999.99',
            ],

            'discounts.*.limit_quantity' => [
                'nullable',
                'integer',
                'min:1',
            ],
        ];
    }

    private function validationMessages(): array
    {
        return [
            'name.required' => 'Vui lòng nhập tên chiến dịch.',
            'start_date.required' => 'Vui lòng nhập thời gian bắt đầu.',
            'end_date.required' => 'Vui lòng nhập thời gian kết thúc.',
            'discounts.required' =>
                'Vui lòng thêm ít nhất một sản phẩm giảm giá.',
            'discounts.min' =>
                'Vui lòng thêm ít nhất một sản phẩm giảm giá.',
            'discounts.*.product_id.required' =>
                'Vui lòng chọn sản phẩm.',
            'discounts.*.product_id.distinct' =>
                'Một sản phẩm không được xuất hiện nhiều lần.',
            'discounts.*.discount_percent.max' =>
                'Phần trăm giảm không được vượt quá 100%.',
            'discounts.*.limit_quantity.min' =>
                'Giới hạn số lượng phải ít nhất là 1.',
        ];
    }

    private function validateBusinessRules(array $validated): void
    {
        if (
            strtotime($validated['end_date'])
            <= strtotime($validated['start_date'])
        ) {
            throw ValidationException::withMessages([
                'end_date' =>
                    'Thời gian kết thúc phải sau thời gian bắt đầu.',
            ]);
        }

        foreach ($validated['discounts'] as $index => $discount) {
            $hasPercent = isset($discount['discount_percent'])
                && $discount['discount_percent'] !== '';

            $hasAmount = isset($discount['discount_amount'])
                && $discount['discount_amount'] !== '';

            if ($hasPercent === $hasAmount) {
                throw ValidationException::withMessages([
                    "discounts.$index.discount_percent" =>
                        'Mỗi sản phẩm phải nhập đúng một loại giảm: phần trăm hoặc số tiền.',
                ]);
            }
        }
    }

    private function insertProductDiscounts(
        int $campaignId,
        array $discounts,
        array $soldByProduct = []
    ): void {
        $now = now();

        $rows = collect($discounts)
            ->map(function (array $discount) use (
                $campaignId,
                $soldByProduct,
                $now
            ): array {
                $productId = (int) $discount['product_id'];
                $soldQuantity = (int) (
                    $soldByProduct[$productId] ?? 0
                );

                $limitQuantity = isset($discount['limit_quantity'])
                    && $discount['limit_quantity'] !== ''
                        ? (int) $discount['limit_quantity']
                        : null;

                if (
                    $limitQuantity !== null
                    && $limitQuantity < $soldQuantity
                ) {
                    throw ValidationException::withMessages([
                        'discounts' =>
                            'Giới hạn mới không được nhỏ hơn số lượng đã bán của sản phẩm ID '
                            . $productId
                            . ' ('
                            . number_format($soldQuantity)
                            . ').',
                    ]);
                }

                return [
                    'campaign_id' => $campaignId,
                    'product_id' => $productId,
                    'discount_percent' =>
                        isset($discount['discount_percent'])
                        && $discount['discount_percent'] !== ''
                            ? (float) $discount['discount_percent']
                            : null,
                    'discount_amount' =>
                        isset($discount['discount_amount'])
                        && $discount['discount_amount'] !== ''
                            ? (float) $discount['discount_amount']
                            : null,
                    'limit_quantity' => $limitQuantity,
                    'sold_quantity' => $soldQuantity,
                    'created_at' => $now,
                    'updated_at' => $now,
                ];
            })
            ->values()
            ->all();

        DB::table('product_discounts')->insert($rows);
    }

    private function productOptions()
    {
        return DB::table('products')
            ->whereNull('deleted_at')
            ->orderBy('name')
            ->get([
                'id',
                'name',
                'status',
            ]);
    }

    private function nullableTrim(mixed $value): ?string
    {
        $value = trim((string) $value);

        return $value !== '' ? $value : null;
    }
}
