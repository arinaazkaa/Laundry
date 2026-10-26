<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasColumn('service_packages', 'service_id')) {
            Schema::table('service_packages', function (Blueprint $table) {
                $table->foreignId('service_id')->nullable()->after('name')
                    ->constrained('services')->nullOnDelete();
            });
        }

        if (! Schema::hasColumn('transaction_items', 'package_id')) {
            Schema::table('transaction_items', function (Blueprint $table) {
                $table->foreignId('package_id')->nullable()->after('service_id')
                    ->constrained('service_packages')->nullOnDelete();
            });
        }

        // Baris paket di transaksi memakai satuan "paket"
        DB::statement("ALTER TABLE transaction_items MODIFY unit_type ENUM('kg', 'pcs', 'paket') NOT NULL");
    }

    public function down(): void
    {
        DB::statement("UPDATE transaction_items SET unit_type = 'pcs' WHERE unit_type = 'paket'");
        DB::statement("ALTER TABLE transaction_items MODIFY unit_type ENUM('kg', 'pcs') NOT NULL");

        Schema::table('transaction_items', function (Blueprint $table) {
            $table->dropConstrainedForeignId('package_id');
        });

        Schema::table('service_packages', function (Blueprint $table) {
            $table->dropConstrainedForeignId('service_id');
        });
    }
};