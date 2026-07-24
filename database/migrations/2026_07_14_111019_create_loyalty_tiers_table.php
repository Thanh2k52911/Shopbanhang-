<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('loyalty_tiers', function (Blueprint $table) {
            $table->id();

            $table->string('name', 100);
            $table->string('code', 50)->unique();

            $table->text('description')->nullable();

            // Tổng tiền tối thiểu để đạt hạng
            $table->decimal('minimum_spending', 15, 2)->default(0);

            // Tổng điểm tối thiểu để đạt hạng
            $table->unsignedBigInteger('minimum_points')->default(0);

            // Hệ số nhân điểm, ví dụ Gold được nhân 1.5
            $table->decimal('point_multiplier', 5, 2)->default(1);

            // Phần trăm giảm giá dành cho hạng thành viên
            $table->decimal('discount_percent', 5, 2)->default(0);

            // Màu hiển thị hạng
            $table->string('color', 30)->nullable();

            $table->string('icon', 500)->nullable();

            $table->unsignedInteger('sort_order')->default(0);

            $table->boolean('status')->default(true);

            $table->timestamps();
            $table->softDeletes();

            $table->index(
                ['status', 'sort_order'],
                'loyalty_tiers_status_sort_idx'
            );
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('loyalty_tiers');
    }
};
