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
        // Add comprehensive indexes for performance
        Schema::table('sales_targets', function (Blueprint $table) {
            // Composite index for matrix queries
            $table->index(['year', 'month', 'salesman_id'], 'idx_targets_period_salesman');
            $table->index(['year', 'month', 'supplier_id'], 'idx_targets_period_supplier');
            $table->index(['year', 'month', 'category_id'], 'idx_targets_period_category');
            
            // Performance index for aggregations
            $table->index(['salesman_id', 'year', 'month'], 'idx_targets_salesman_period');
            $table->index(['supplier_id', 'category_id'], 'idx_targets_supplier_category');
            
            // Index for scoped queries
            $table->index(['region_id', 'channel_id'], 'idx_targets_region_channel');
        });

        Schema::table('salesmen', function (Blueprint $table) {
            // Optimize salesman lookups
            $table->index(['region_id', 'channel_id'], 'idx_salesmen_scope');
            $table->index(['name'], 'idx_salesmen_name');
            $table->index(['employee_code'], 'idx_salesmen_employee_code');
            $table->index(['classification'], 'idx_salesmen_classification');
        });

        Schema::table('suppliers', function (Blueprint $table) {
            // Optimize supplier queries
            $table->index(['classification', 'name'], 'idx_suppliers_class_name');
        });

        Schema::table('categories', function (Blueprint $table) {
            // Optimize category lookups
            $table->index(['supplier_id', 'name'], 'idx_categories_supplier_name');
        });

        // Only add index if the table exists
        if (Schema::hasTable('salesman_classifications')) {
            Schema::table('salesman_classifications', function (Blueprint $table) {
                // Optimize classification queries
                $table->index(['salesman_id', 'classification'], 'idx_salesman_class');
            });
        }

        // Database-level optimizations commented out due to privilege requirements
        // These should be configured at server level, not in migrations
        /*
        DB::unprepared('
            -- Optimize MySQL configuration
            SET GLOBAL innodb_buffer_pool_size = 268435456; -- 256MB
            SET GLOBAL query_cache_size = 67108864; -- 64MB
            SET GLOBAL key_buffer_size = 33554432; -- 32MB
        ');
        */
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('sales_targets', function (Blueprint $table) {
            $table->dropIndex('idx_targets_period_salesman');
            $table->dropIndex('idx_targets_period_supplier');
            $table->dropIndex('idx_targets_period_category');
            $table->dropIndex('idx_targets_salesman_period');
            $table->dropIndex('idx_targets_supplier_category');
            $table->dropIndex('idx_targets_region_channel');
        });

        Schema::table('salesmen', function (Blueprint $table) {
            $table->dropIndex('idx_salesmen_scope_active');
            $table->dropIndex('idx_salesmen_active_name');
        });

        Schema::table('suppliers', function (Blueprint $table) {
            $table->dropIndex('idx_suppliers_class_name');
        });

        Schema::table('categories', function (Blueprint $table) {
            $table->dropIndex('idx_categories_supplier_name');
        });

        // Only drop index if the table exists
        if (Schema::hasTable('salesman_classifications')) {
            Schema::table('salesman_classifications', function (Blueprint $table) {
                $table->dropIndex('idx_salesman_class');
            });
        }
    }
};
