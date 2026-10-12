<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('promos', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->text('description')->nullable();
            $table->enum('type', [
                'gratis_ongkir', 'diskon_persen', 'diskon_nominal',
                'berdasarkan_berat', 'minimal_transaksi', 'member',
            ]);
            $table->decimal('value', 12, 2)->nullable();
            $table->decimal('min_transaction', 12, 2)->nullable();
            $table->decimal('min_weight', 8, 2)->nullable();
            $table->date('start_date');
            $table->date('end_date');
            $table->time('start_time')->nullable();
            $table->time('end_time')->nullable();
            $table->integer('quota')->nullable();
            $table->integer('used_count')->default(0);
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('promos');
    }
};