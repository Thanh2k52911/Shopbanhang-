<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('orders', function (Blueprint $table): void {
            if (! Schema::hasColumn('orders', 'shipping_method_id')) {
                $table->foreignId('shipping_method_id')
                    ->nullable()
                    ->after('warehouse_id')
                    ->constrained('shipping_methods')
                    ->nullOnDelete();
            }
        });
    }

    public function down(): void
    {
        Schema::table('orders', function (Blueprint $table): void {
            if (Schema::hasColumn('orders', 'shipping_method_id')) {
                $table->dropConstrainedForeignId('shipping_method_id');
            }
        });
    }
};
