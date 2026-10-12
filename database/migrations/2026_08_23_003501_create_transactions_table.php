<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('transactions', function (Blueprint $table) {
            $table->id();
            $table->string('code')->unique();

            $table->foreignId('customer_id')->nullable()
                ->constrained('customers')->nullOnDelete();

            $table->foreignId('cashier_id')
                ->constrained('users')->restrictOnDelete();

            $table->decimal('subtotal', 12, 2)->default(0);
            $table->decimal('discount', 12, 2)->default(0);
            $table->decimal('delivery_fee', 12, 2)->default(0);
            $table->decimal('total', 12, 2)->default(0);
            $table->decimal('paid_amount', 12, 2)->default(0);

            $table->enum('payment_status', ['belum_bayar', 'dp', 'lunas'])
                ->default('belum_bayar')->index();

            $table->string('payment_method')->nullable();

            $table->enum('status', [
                'menunggu', 'diproses', 'dicuci', 'dikeringkan',
                'disetrika', 'siap_diambil', 'selesai', 'dibatalkan',
            ])->default('menunggu')->index();

            $table->foreignId('promo_id')->nullable()
                ->constrained('promos')->nullOnDelete();

            $table->boolean('use_pickup')->default(false);
            $table->boolean('use_delivery')->default(false);
            $table->date('estimated_done_at')->nullable();
            $table->text('notes')->nullable();

            $table->timestamps();
            $table->index(['status', 'created_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('transactions');
    }
};