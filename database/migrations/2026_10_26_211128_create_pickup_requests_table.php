<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('pickup_requests', function (Blueprint $table) {
            $table->id();
            $table->foreignId('customer_id')->constrained()->cascadeOnDelete();
            $table->text('address');
            $table->date('preferred_date');
            $table->enum('time_slot', ['pagi', 'siang', 'sore']);
            $table->string('notes', 300)->nullable();
            $table->enum('status', ['menunggu', 'dikonfirmasi', 'dijemput', 'ditolak', 'dibatalkan'])
                ->default('menunggu')->index();
            $table->string('staff_note', 300)->nullable();
            $table->foreignId('handled_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('handled_at')->nullable();
            $table->foreignId('transaction_id')->nullable()->constrained()->nullOnDelete();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('pickup_requests');
    }
};