<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations for performance optimization.
     */
    public function up(): void
    {
        // Check and add indexes safely
        $this->addIndexIfNotExists('sales_targets', [
            ['columns' => ['year', 'month'], 'name' => 'idx_targets_period'],
            ['columns' => ['salesman_id', 'year', 'month'], 'name' => 'idx_targets_salesman_period'],
            ['columns' => ['supplier_id', 'category_id'], 'name' => 'idx_targets_supplier_category'],
            ['columns' => ['year', 'month', 'supplier_id'], 'name' => 'idx_targets_period_supplier'],
            ['columns' => ['year', 'month', 'category_id'], 'name' => 'idx_targets_period_category'],
        ]);

        $this->addIndexIfNotExists('salesmen', [
            ['columns' => ['region_id', 'channel_id'], 'name' => 'idx_salesmen_scope'],
            ['columns' => ['name'], 'name' => 'idx_salesmen_name'],
            ['columns' => ['employee_code'], 'name' => 'idx_salesmen_employee_code'],
            ['columns' => ['classification'], 'name' => 'idx_salesmen_classification'],
        ]);

        $this->addIndexIfNotExists('suppliers', [
            ['columns' => ['classification'], 'name' => 'idx_suppliers_classification'],
            ['columns' => ['name'], 'name' => 'idx_suppliers_name'],
        ]);

        $this->addIndexIfNotExists('categories', [
            ['columns' => ['supplier_id', 'name'], 'name' => 'idx_categories_supplier_name'],
            ['columns' => ['name'], 'name' => 'idx_categories_name'],
        ]);

        // Add indexes for other tables if they exist
        if (Schema::hasTable('regions')) {
            $this->addIndexIfNotExists('regions', [
                ['columns' => ['name'], 'name' => 'idx_regions_name'],
            ]);
        }

        if (Schema::hasTable('channels')) {
            $this->addIndexIfNotExists('channels', [
                ['columns' => ['name'], 'name' => 'idx_channels_name'],
            ]);
        }

        if (Schema::hasTable('active_month_years')) {
            $this->addIndexIfNotExists('active_month_years', [
                ['columns' => ['year', 'month'], 'name' => 'idx_active_periods'],
                ['columns' => ['is_open'], 'name' => 'idx_active_periods_open'],
            ]);
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Drop indexes safely
        $this->dropIndexIfExists('sales_targets', [
            'idx_targets_period',
            'idx_targets_salesman_period',
            'idx_targets_supplier_category',
            'idx_targets_period_supplier',
            'idx_targets_period_category',
        ]);

        $this->dropIndexIfExists('salesmen', [
            'idx_salesmen_scope',
            'idx_salesmen_name',
            'idx_salesmen_employee_code',
            'idx_salesmen_classification',
        ]);

        $this->dropIndexIfExists('suppliers', [
            'idx_suppliers_classification',
            'idx_suppliers_name',
        ]);

        $this->dropIndexIfExists('categories', [
            'idx_categories_supplier_name',
            'idx_categories_name',
        ]);

        if (Schema::hasTable('regions')) {
            $this->dropIndexIfExists('regions', ['idx_regions_name']);
        }

        if (Schema::hasTable('channels')) {
            $this->dropIndexIfExists('channels', ['idx_channels_name']);
        }

        if (Schema::hasTable('active_month_years')) {
            $this->dropIndexIfExists('active_month_years', [
                'idx_active_periods',
                'idx_active_periods_open',
            ]);
        }
    }

    /**
     * Add index if it doesn't exist
     */
    private function addIndexIfNotExists(string $table, array $indexes): void
    {
        foreach ($indexes as $indexConfig) {
            $indexName = $indexConfig['name'];
            $columns = $indexConfig['columns'];
            
            // Check if index exists using Laravel's Schema methods
            try {
                // Try to add the index, Laravel will throw an exception if it already exists
                Schema::table($table, function (Blueprint $tableBlueprint) use ($columns, $indexName) {
                    $tableBlueprint->index($columns, $indexName);
                });
                echo "Added index {$indexName} to {$table}\n";
            } catch (\Exception $e) {
                // Index likely already exists
                if (strpos($e->getMessage(), 'already exists') !== false || 
                    strpos($e->getMessage(), 'Duplicate') !== false ||
                    strpos($e->getMessage(), 'duplicate') !== false) {
                    echo "Index {$indexName} already exists on {$table}\n";
                } else {
                    // Re-throw if it's a different error
                    throw $e;
                }
            }
        }
    }

    /**
     * Drop index if it exists
     */
    private function dropIndexIfExists(string $table, array $indexNames): void
    {
        foreach ($indexNames as $indexName) {
            try {
                $exists = collect(DB::select("SHOW INDEX FROM {$table}"))->contains('Key_name', $indexName);
                
                if ($exists) {
                    Schema::table($table, function (Blueprint $tableBlueprint) use ($indexName) {
                        $tableBlueprint->dropIndex($indexName);
                    });
                    echo "Dropped index {$indexName} from {$table}\n";
                }
            } catch (\Exception $e) {
                echo "Could not drop index {$indexName} from {$table}: " . $e->getMessage() . "\n";
            }
        }
    }
};
