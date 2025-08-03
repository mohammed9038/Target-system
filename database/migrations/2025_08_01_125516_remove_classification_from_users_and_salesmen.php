<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Remove classification column from users table (now using pivot table)
        if (Schema::hasColumn('users', 'classification')) {
            Schema::table('users', function (Blueprint $table) {
                // Drop any indexes on the classification column first
                try {
                    $table->dropIndex('idx_users_classification');
                } catch (\Exception $e) {
                    // Index might not exist, continue
                }
                $table->dropColumn('classification');
            });
        }

        // Remove classification column from salesmen table (now using pivot table)
        if (Schema::hasColumn('salesmen', 'classification')) {
            Schema::table('salesmen', function (Blueprint $table) {
                // Drop any indexes on the classification column first
                try {
                    $table->dropIndex('idx_salesmen_classification');
                } catch (\Exception $e) {
                    // Index might not exist, continue
                }
                $table->dropColumn('classification');
            });
        }
    }

    public function down(): void
    {
        // Add back classification columns if needed
        Schema::table('users', function (Blueprint $table) {
            $table->enum('classification', ['food', 'non_food', 'both'])->nullable()->after('role');
        });

        Schema::table('salesmen', function (Blueprint $table) {
            $table->enum('classification', ['food', 'non_food', 'both'])->nullable()->after('channel_id');
        });
    }
};