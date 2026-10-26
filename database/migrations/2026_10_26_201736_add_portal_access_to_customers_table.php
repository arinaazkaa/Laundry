<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('customers', function (Blueprint $table) {
            if (! Schema::hasColumn('customers', 'phone_normalized')) {
                $table->string('phone_normalized', 20)->nullable()->index()->after('phone');
            }
            if (! Schema::hasColumn('customers', 'password')) {
                $table->string('password')->nullable()->after('is_member');
            }
            if (! Schema::hasColumn('customers', 'remember_token')) {
                $table->rememberToken()->after('password');
            }
            if (! Schema::hasColumn('customers', 'activation_code_hash')) {
                $table->string('activation_code_hash')->nullable()->after('remember_token');
            }
            if (! Schema::hasColumn('customers', 'activation_expires_at')) {
                $table->timestamp('activation_expires_at')->nullable()->after('activation_code_hash');
            }
            if (! Schema::hasColumn('customers', 'activated_at')) {
                $table->timestamp('activated_at')->nullable()->after('activation_expires_at');
            }
        });

        // Isi nomor yang sudah dirapikan untuk pelanggan yang sudah ada
        DB::table('customers')->orderBy('id')->chunkById(200, function ($rows) {
            foreach ($rows as $row) {
                DB::table('customers')->where('id', $row->id)
                    ->update(['phone_normalized' => $this->normalize($row->phone)]);
            }
        });
    }

    public function down(): void
    {
        Schema::table('customers', function (Blueprint $table) {
            $table->dropColumn([
                'phone_normalized', 'password', 'remember_token',
                'activation_code_hash', 'activation_expires_at', 'activated_at',
            ]);
        });
    }

    private function normalize(?string $phone): ?string
    {
        $d = preg_replace('/\D/', '', (string) $phone);

        if ($d === '') {
            return null;
        }

        if (str_starts_with($d, '62')) {
            // sudah format internasional
        } elseif (str_starts_with($d, '0')) {
            $d = '62' . substr($d, 1);
        } elseif (str_starts_with($d, '8')) {
            $d = '62' . $d;
        } else {
            return null;
        }

        return (strlen($d) >= 10 && strlen($d) <= 15) ? $d : null;
    }
};