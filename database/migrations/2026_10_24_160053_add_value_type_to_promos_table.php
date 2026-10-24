<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasColumn('promos', 'value_type')) {
            Schema::table('promos', function (Blueprint $table) {
                $table->enum('value_type', ['persen', 'nominal'])->default('nominal')->after('value');
            });
        }

        // Promo "Diskon Persen" yang sudah ada tetap dihitung sebagai persen
        DB::table('promos')->where('type', 'diskon_persen')->update(['value_type' => 'persen']);
    }

    public function down(): void
    {
        Schema::table('promos', function (Blueprint $table) {
            $table->dropColumn('value_type');
        });
    }
};