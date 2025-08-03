<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        // SQLite doesn't support ALTER COLUMN for ENUM, so we need to recreate the table
        Schema::table('suppliers', function (Blueprint $table) {
            // Add temporary column
            $table->enum('classification_new', ['food', 'non_food', 'both'])->after('classification');
        });
        
        // Copy data to new column
        DB::statement("UPDATE suppliers SET classification_new = classification");
        
        Schema::table('suppliers', function (Blueprint $table) {
            // Drop any indexes on the classification column first
            try {
                $table->dropIndex('idx_suppliers_class_name');
            } catch (\Exception $e) {
                // Index might not exist, continue
            }
            try {
                $table->dropIndex('idx_suppliers_classification');
            } catch (\Exception $e) {
                // Index might not exist, continue
            }
            // Drop old column
            $table->dropColumn('classification');
        });
        
        Schema::table('suppliers', function (Blueprint $table) {
            $table->renameColumn('classification_new', 'classification');
        });
    }

    public function down(): void
    {
        Schema::table('suppliers', function (Blueprint $table) {
            // Add temporary column with old enum
            $table->enum('classification_new', ['food', 'non_food'])->after('classification');
        });
        
        // Copy data, setting 'both' to 'food' as fallback
        DB::statement("UPDATE suppliers SET classification_new = CASE WHEN classification = 'both' THEN 'food' ELSE classification END");
        
        Schema::table('suppliers', function (Blueprint $table) {
            $table->dropColumn('classification');
        });
        
        Schema::table('suppliers', function (Blueprint $table) {
            $table->renameColumn('classification_new', 'classification');
        });
    }
};