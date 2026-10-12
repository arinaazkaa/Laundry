<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('pickup_deliveries', function (Blueprint $table) {
            $table->id();
            $table->foreignId('transaction_id')->constrained()->cascadeOnDelete();
            $table->enum('type', ['pickup', 'delivery']);
            $table->enum('status', ['dijadwalkan', 'dalam_perjalanan', 'selesai', 'dibatalkan'])
                ->default('dijadwalkan');
            $table->dateTime('scheduled_at')->nullable();
            $table->text('address')->nullable();
            $table->text('notes')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('pickup_deliveries');
    }
};