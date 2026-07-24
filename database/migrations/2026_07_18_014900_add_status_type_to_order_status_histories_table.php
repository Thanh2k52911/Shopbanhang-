<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('order_status_histories', function (Blueprint $table): void {
            if (! Schema::hasColumn('order_status_histories', 'status_type')) {
                $table->string('status_type', 30)
                    ->default('order')
                    ->after('to_status');

                $table->index(
                    ['order_id', 'status_type'],
                    'order_status_histories_order_type_index'
                );
            }
        });
    }

    public function down(): void
    {
        Schema::table('order_status_histories', function (Blueprint $table): void {
            if (Schema::hasColumn('order_status_histories', 'status_type')) {
                $table->dropIndex(
                    'order_status_histories_order_type_index'
                );

                $table->dropColumn('status_type');
            }
        });
    }
};
