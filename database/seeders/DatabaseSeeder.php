<?php

namespace Database\Seeders;

use App\Models\BusinessSetting;
use App\Models\Service;
use App\Models\User;
use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        // Roles
        foreach (['pemilik', 'kasir', 'pegawai'] as $roleName) {
            Role::firstOrCreate(['name' => $roleName]);
        }

        // Users
        $owner = User::firstOrCreate(
            ['email' => 'owner@laundry.test'],
            ['name' => 'Pemilik Laundry', 'password' => bcrypt('password')]
        );
        $owner->assignRole('pemilik');

        $cashier = User::firstOrCreate(
            ['email' => 'kasir@laundry.test'],
            ['name' => 'Andi', 'password' => bcrypt('password')]
        );
        $cashier->assignRole('kasir');

        $staff = User::firstOrCreate(
            ['email' => 'pegawai@laundry.test'],
            ['name' => 'Budi', 'password' => bcrypt('password')]
        );
        $staff->assignRole('pegawai');

        // Layanan default
        $services = [
            ['name' => 'Cuci + Kering', 'type' => 'kiloan', 'price' => 7000],
            ['name' => 'Cuci + Kering + Setrika', 'type' => 'kiloan', 'price' => 9000],
            ['name' => 'Setrika Saja', 'type' => 'kiloan', 'price' => 5000],
            ['name' => 'Kemeja', 'type' => 'satuan', 'price' => 5000],
            ['name' => 'Jas', 'type' => 'satuan', 'price' => 15000],
            ['name' => 'Selimut', 'type' => 'satuan', 'price' => 15000],
            ['name' => 'Bed Cover', 'type' => 'satuan', 'price' => 25000],
            ['name' => 'Karpet', 'type' => 'satuan', 'price' => 30000],
        ];
        foreach ($services as $s) {
            Service::firstOrCreate(['name' => $s['name']], $s);
        }

        // Pengaturan bisnis default
        $settings = [
            'business_name' => 'Laundry Desa',
            'business_address' => 'Jl. Contoh No. 1',
            'business_whatsapp' => '6281234567890',
            'pickup_enabled' => '1',
            'delivery_enabled' => '1',
            'pickup_fee' => '5000',
            'delivery_fee' => '5000',
            'free_ongkir_enabled' => '1',
            'free_ongkir_min_weight' => '5',
            'work_duration_days' => '2',

            'member_min_transactions' => '5',      // pelanggan direkomendasikan jadi member setelah 5x transaksi
            'member_min_spent' => '200000',        // ATAU setelah total belanja Rp200.000
            'member_discount_percent' => '5',      // benefit member: diskon otomatis 5% tiap transaksi
        ];
        foreach ($settings as $key => $value) {
            BusinessSetting::firstOrCreate(['key' => $key], ['value' => $value]);
        }
    }
}