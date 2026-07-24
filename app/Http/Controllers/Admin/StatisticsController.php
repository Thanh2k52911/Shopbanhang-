<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Services\Admin\StatisticsService;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class StatisticsController extends Controller
{
    public function __construct(
        private readonly StatisticsService $statisticsService
    ) {
    }

    /**
     * Trang thống kê tổng hợp.
     */
    public function index(Request $request): View
    {
        $validated = $request->validate([
            'range' => [
                'nullable',
                Rule::in([
                    '7_days',
                    '30_days',
                    '90_days',
                    'this_month',
                    'last_month',
                    'this_year',
                    'custom',
                ]),
            ],

            'date_from' => [
                'nullable',
                'date',
            ],

            'date_to' => [
                'nullable',
                'date',
                'after_or_equal:date_from',
            ],
        ], [
            'range.in' =>
                'Khoảng thời gian thống kê không hợp lệ.',

            'date_from.date' =>
                'Ngày bắt đầu không hợp lệ.',

            'date_to.date' =>
                'Ngày kết thúc không hợp lệ.',

            'date_to.after_or_equal' =>
                'Ngày kết thúc phải từ ngày bắt đầu trở đi.',
        ]);

        [
            $dateFrom,
            $dateTo,
            $range,
        ] = $this->resolveDateRange($validated);

        $data = $this->statisticsService
            ->getDashboardData(
                $dateFrom,
                $dateTo
            );

        return view(
            'admin.statistics.index',
            array_merge(
                $data,
                [
                    'dateFrom' => $dateFrom,
                    'dateTo' => $dateTo,
                    'range' => $range,
                ]
            )
        );
    }

    /**
     * @return array{0: Carbon, 1: Carbon, 2: string}
     */
    private function resolveDateRange(
        array $validated
    ): array {
        $range = $validated['range']
            ?? '30_days';

        return match ($range) {
            '7_days' => [
                now()->subDays(6)->startOfDay(),
                now()->endOfDay(),
                $range,
            ],

            '90_days' => [
                now()->subDays(89)->startOfDay(),
                now()->endOfDay(),
                $range,
            ],

            'this_month' => [
                now()->startOfMonth()->startOfDay(),
                now()->endOfDay(),
                $range,
            ],

            'last_month' => [
                now()
                    ->subMonthNoOverflow()
                    ->startOfMonth()
                    ->startOfDay(),

                now()
                    ->subMonthNoOverflow()
                    ->endOfMonth()
                    ->endOfDay(),

                $range,
            ],

            'this_year' => [
                now()->startOfYear()->startOfDay(),
                now()->endOfDay(),
                $range,
            ],

            'custom' => [
                Carbon::parse(
                    $validated['date_from']
                    ?? now()->subDays(29)
                )->startOfDay(),

                Carbon::parse(
                    $validated['date_to']
                    ?? now()
                )->endOfDay(),

                $range,
            ],

            default => [
                now()->subDays(29)->startOfDay(),
                now()->endOfDay(),
                '30_days',
            ],
        };
    }
}
