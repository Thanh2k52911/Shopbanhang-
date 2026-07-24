<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        $this->addIndexIfMissing(
            'orders',
            ['created_at'],
            'orders_created_at_index'
        );

        $this->addIndexIfMissing(
            'payments',
            ['status'],
            'payments_status_index'
        );

        $this->addIndexIfMissing(
            'shipments',
            ['status'],
            'shipments_status_index'
        );

        $this->addIndexIfMissing(
            'inventories',
            ['warehouse_id', 'sku_id'],
            'inventories_warehouse_sku_index'
        );

        $this->addIndexIfMissing(
            'notifications',
            ['read_at'],
            'notifications_read_at_index'
        );

        $this->addIndexIfMissing(
            'search_histories',
            ['created_at'],
            'search_histories_created_at_index'
        );

        $this->addIndexIfMissing(
            'login_histories',
            ['logged_in_at'],
            'login_histories_logged_in_at_index'
        );
    }

    public function down(): void
    {
        $this->dropIndexIfExists(
            'orders',
            'orders_created_at_index'
        );

        $this->dropIndexIfExists(
            'payments',
            'payments_status_index'
        );

        $this->dropIndexIfExists(
            'shipments',
            'shipments_status_index'
        );

        $this->dropIndexIfExists(
            'inventories',
            'inventories_warehouse_sku_index'
        );

        $this->dropIndexIfExists(
            'notifications',
            'notifications_read_at_index'
        );

        $this->dropIndexIfExists(
            'search_histories',
            'search_histories_created_at_index'
        );

        $this->dropIndexIfExists(
            'login_histories',
            'login_histories_logged_in_at_index'
        );
    }

    private function addIndexIfMissing(
        string $tableName,
        array $columns,
        string $indexName
    ): void {
        if (! Schema::hasTable($tableName)) {
            return;
        }

        foreach ($columns as $column) {
            if (! Schema::hasColumn($tableName, $column)) {
                return;
            }
        }

        if ($this->indexExists($tableName, $indexName)) {
            return;
        }

        Schema::table(
            $tableName,
            function (Blueprint $table) use (
                $columns,
                $indexName
            ): void {
                $table->index($columns, $indexName);
            }
        );
    }

    private function dropIndexIfExists(
        string $tableName,
        string $indexName
    ): void {
        if (
            ! Schema::hasTable($tableName)
            || ! $this->indexExists($tableName, $indexName)
        ) {
            return;
        }

        Schema::table(
            $tableName,
            function (Blueprint $table) use ($indexName): void {
                $table->dropIndex($indexName);
            }
        );
    }

    private function indexExists(
        string $tableName,
        string $indexName
    ): bool {
        $databaseName = DB::connection()->getDatabaseName();

        return DB::table('information_schema.statistics')
            ->where('table_schema', $databaseName)
            ->where('table_name', $tableName)
            ->where('index_name', $indexName)
            ->exists();
    }
};
