<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('sales_targets', function (Blueprint $table) {
            $table->id();
            $table->year('year');
            $table->unsignedTinyInteger('month');
            $table->foreignId('region_id')->constrained();
            $table->foreignId('channel_id')->constrained();
            $table->foreignId('salesman_id')->constrained();
            $table->foreignId('supplier_id')->constrained();
            $table->foreignId('category_id')->constrained();
            $table->decimal('target_amount', 15, 2);
            $table->text('notes')->nullable();
            $table->timestamps();
            
            // Unique constraint as per PRD
            $table->unique(['year', 'month', 'salesman_id', 'supplier_id', 'category_id'], 'st_unique_idx');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('sales_targets');
    }
}; 