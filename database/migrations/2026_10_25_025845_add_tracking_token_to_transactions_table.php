<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasColumn('transactions', 'tracking_token')) {
            Schema::table('transactions', function (Blueprint $table) {
                $table->string('tracking_token', 40)->nullable()->unique()->after('code');
            });
        }

        // Beri token acak untuk transaksi yang sudah ada
        DB::table('transactions')->whereNull('tracking_token')->chunkById(100, function ($rows) {
            foreach ($rows as $row) {
                DB::table('transactions')->where('id', $row->id)
                    ->update(['tracking_token' => Str::lower(Str::random(16))]);
            }
        });
    }

    public function down(): void
    {
        Schema::table('transactions', function (Blueprint $table) {
            $table->dropUnique(['tracking_token']);
            $table->dropColumn('tracking_token');
        });
    }
};