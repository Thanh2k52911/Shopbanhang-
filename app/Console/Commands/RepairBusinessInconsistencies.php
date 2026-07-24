<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class RepairBusinessInconsistencies extends Command
{
    protected $signature = 'project:repair-business {--dry-run}';
    protected $description = 'Đồng bộ phí shipment và đưa return completed chưa hoàn tiền về processing.';

    public function handle(): int
    {
        $shipments = DB::table('shipments as s')
            ->join('orders as o', 'o.id', '=', 's.order_id')
            ->whereColumn('s.shipping_fee', '!=', 'o.shipping_fee')
            ->select('s.id','s.shipment_code','s.shipping_fee','o.shipping_fee')
            ->get();

        $returns = DB::table('return_requests as rr')
            ->leftJoin('refunds as r', function ($join): void {
                $join->on('r.return_request_id', '=', 'rr.id')
                    ->where('r.status', '=', 'completed');
            })
            ->where('rr.status', 'completed')
            ->groupBy('rr.id','rr.return_code','rr.approved_amount','rr.requested_amount')
            ->havingRaw('COALESCE(SUM(r.amount),0) + 0.01 < CASE WHEN rr.approved_amount > 0 THEN rr.approved_amount ELSE rr.requested_amount END')
            ->selectRaw('rr.id, rr.return_code, COALESCE(SUM(r.amount),0) as refunded')
            ->get();

        $this->info("Shipment lệch phí: {$shipments->count()}");
        $this->info("Return hoàn tất sai: {$returns->count()}");

        if ($this->option('dry-run')) {
            return self::SUCCESS;
        }

        DB::transaction(function () use ($shipments, $returns): void {
            foreach ($shipments as $row) {
                DB::table('shipments')->where('id',$row->id)->update([
                    'shipping_fee' => $row->shipping_fee,
                    'updated_at' => now(),
                ]);
            }
            foreach ($returns as $row) {
                DB::table('return_requests')->where('id',$row->id)->update([
                    'status' => 'processing',
                    'completed_at' => null,
                    'updated_at' => now(),
                ]);
            }
        },3);

        $this->info('Đã sửa dữ liệu không nhất quán.');
        return self::SUCCESS;
    }
}
