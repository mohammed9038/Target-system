<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Add indexes to sales_targets table for matrix queries
        Schema::table('sales_targets', function (Blueprint $table) {
            try {
                $table->index(['year', 'month'], 'sales_targets_year_month_index');
            } catch (\Exception $e) {
                // Index might already exist
            }
            try {
                $table->index(['salesman_id', 'supplier_id', 'category_id'], 'sales_targets_salesman_supplier_category_index');
            } catch (\Exception $e) {
                // Index might already exist
            }
            try {
                $table->index(['year', 'month', 'salesman_id'], 'sales_targets_lookup_index');
            } catch (\Exception $e) {
                // Index might already exist
            }
        });

        // Add indexes to salesmen table
        Schema::table('salesmen', function (Blueprint $table) {
            try {
                $table->index(['region_id', 'channel_id'], 'salesmen_region_channel_index');
            } catch (\Exception $e) {
                // Index might already exist
            }
            try {
                $table->index('name', 'salesmen_name_index');
            } catch (\Exception $e) {
                // Index might already exist
            }
        });

        // Add indexes to suppliers table
        Schema::table('suppliers', function (Blueprint $table) {
            try {
                $table->index('classification', 'suppliers_classification_index');
            } catch (\Exception $e) {
                // Index might already exist
            }
            try {
                $table->index('name', 'suppliers_name_index');
            } catch (\Exception $e) {
                // Index might already exist
            }
        });

        // Add indexes to categories table
        Schema::table('categories', function (Blueprint $table) {
            try {
                $table->index('supplier_id', 'categories_supplier_id_index');
            } catch (\Exception $e) {
                // Index might already exist
            }
            try {
                $table->index('name', 'categories_name_index');
            } catch (\Exception $e) {
                // Index might already exist
            }
        });

        // Add indexes to salesman_classifications table
        Schema::table('salesman_classifications', function (Blueprint $table) {
            try {
                $table->index('salesman_id', 'salesman_classifications_salesman_id_index');
            } catch (\Exception $e) {
                // Index might already exist
            }
            try {
                $table->index('classification', 'salesman_classifications_classification_index');
            } catch (\Exception $e) {
                // Index might already exist
            }
        });

        // Add indexes to active_months_years table
        Schema::table('active_months_years', function (Blueprint $table) {
            try {
                $table->index(['year', 'month'], 'active_months_years_year_month_index');
            } catch (\Exception $e) {
                // Index might already exist
            }
            try {
                $table->index('is_open', 'active_months_years_is_open_index');
            } catch (\Exception $e) {
                // Index might already exist
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Remove indexes - silently fail if they don't exist
        try {
            Schema::table('sales_targets', function (Blueprint $table) {
                $table->dropIndex('sales_targets_year_month_index');
                $table->dropIndex('sales_targets_salesman_supplier_category_index');
                $table->dropIndex('sales_targets_lookup_index');
            });
        } catch (\Exception $e) {
            // Ignore errors if indexes don't exist
        }

        try {
            Schema::table('salesmen', function (Blueprint $table) {
                $table->dropIndex('salesmen_region_channel_index');
                $table->dropIndex('salesmen_name_index');
            });
        } catch (\Exception $e) {
            // Ignore errors if indexes don't exist
        }

        try {
            Schema::table('suppliers', function (Blueprint $table) {
                $table->dropIndex('suppliers_classification_index');
                $table->dropIndex('suppliers_name_index');
            });
        } catch (\Exception $e) {
            // Ignore errors if indexes don't exist
        }

        try {
            Schema::table('categories', function (Blueprint $table) {
                $table->dropIndex('categories_supplier_id_index');
                $table->dropIndex('categories_name_index');
            });
        } catch (\Exception $e) {
            // Ignore errors if indexes don't exist
        }

        try {
            Schema::table('salesman_classifications', function (Blueprint $table) {
                $table->dropIndex('salesman_classifications_salesman_id_index');
                $table->dropIndex('salesman_classifications_classification_index');
            });
        } catch (\Exception $e) {
            // Ignore errors if indexes don't exist
        }

        try {
            Schema::table('active_months_years', function (Blueprint $table) {
                $table->dropIndex('active_months_years_year_month_index');
                $table->dropIndex('active_months_years_is_open_index');
            });
        } catch (\Exception $e) {
            // Ignore errors if indexes don't exist
        }
    }
};
