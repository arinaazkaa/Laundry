<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('inventory_items', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('unit'); // liter, kg, pcs, dll
            $table->decimal('current_stock', 10, 2)->default(0);
            $table->decimal('min_stock', 10, 2)->default(0);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('inventory_items');
    }
};