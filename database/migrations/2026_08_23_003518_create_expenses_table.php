<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('expenses', function (Blueprint $table) {
            $table->id();
            $table->date('date');
            $table->enum('category', [
                'detergen', 'pewangi', 'plastik', 'listrik', 'air',
                'gas', 'perawatan_mesin', 'transportasi', 'gaji', 'lainnya',
            ]);
            $table->string('description')->nullable();
            $table->decimal('amount', 12, 2);
            $table->text('notes')->nullable();
            $table->foreignId('created_by')->constrained('users');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('expenses');
    }
};