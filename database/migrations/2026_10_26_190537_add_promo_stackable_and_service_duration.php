<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasColumn('promos', 'stackable')) {
            Schema::table('promos', function (Blueprint $table) {
                $table->boolean('stackable')->default(false)->after('is_active');
            });
        }

        if (! Schema::hasColumn('services', 'duration_days')) {
            Schema::table('services', function (Blueprint $table) {
                $table->unsignedTinyInteger('duration_days')->nullable()->after('price');
            });
        }
    }

    public function down(): void
    {
        Schema::table('promos', function (Blueprint $table) {
            $table->dropColumn('stackable');
        });

        Schema::table('services', function (Blueprint $table) {
            $table->dropColumn('duration_days');
        });
    }
};