<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('service_packages', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->decimal('weight_kg', 8, 2);
            $table->text('description')->nullable();
            $table->decimal('normal_price', 12, 2);
            $table->decimal('package_price', 12, 2);
            $table->date('start_date')->nullable();
            $table->date('end_date')->nullable();
            $table->integer('quota')->nullable();
            $table->integer('used_count')->default(0);
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('service_packages');
    }
};