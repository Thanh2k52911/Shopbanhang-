<?php

namespace App\Http\Controllers\Client;

use App\Http\Controllers\Controller;
use App\Models\LoyaltyTransaction;
use App\Services\LoyaltyService;
use Illuminate\Http\Request;
use Illuminate\View\View;

class LoyaltyController extends Controller
{
    public function index(
        Request $request,
        LoyaltyService $loyaltyService
    ): View {
        $account =
            $loyaltyService->getOrCreateAccount(
                $request->user()
            );

        $account->loadMissing([
            'tier',
            'highestTier',
            'tierRewards.coupon',
        ]);

        $nextTier =
            $loyaltyService->getNextTier(
                $account
            );

        $tierProgress =
            $loyaltyService->getTierProgress(
                $account,
                $nextTier
            );

        $transactions =
            LoyaltyTransaction::query()
                ->where(
                    'loyalty_account_id',
                    $account->id
                )
                ->latest()
                ->paginate(15)
                ->withQueryString();

        return view(
            'client.account.loyalty.index',
            compact(
                'account',
                'nextTier',
                'tierProgress',
                'transactions'
            )
        );
    }
}
