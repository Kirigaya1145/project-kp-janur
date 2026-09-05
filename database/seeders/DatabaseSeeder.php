<?php

namespace Database\Seeders;

// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use App\Models\CompanyProfile;
use App\Models\RuteHarga;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        User::updateOrCreate(
            ['email' => 'admin@janurtangguhabadi.com'],
            [
                'nama' => 'Admin Janur',
                'password' => Hash::make('password'),
                'role' => 'admin',
            ]
        );

        User::updateOrCreate(
            ['email' => 'staff@janurtangguhabadi.com'],
            [
                'nama' => 'Staff Operasional',
                'password' => Hash::make('password'),
                'role' => 'staff',
            ]
        );

        User::updateOrCreate(
            ['email' => 'customer@example.com'],
            [
                'nama' => 'Customer Demo',
                'password' => Hash::make('password'),
                'role' => 'customer',
            ]
        );

        CompanyProfile::updateOrCreate(
            ['profile_id' => 1],
            [
                'nama_perusahaan' => 'PT. Janur Tangguh Abadi',
                'alamat' => 'Jl. Ikan Sepat IV No. 26, Tanjung Perak, Surabaya 60177',
                'email' => 'info@janurtangguhabadi.com',
                'telepon' => '+62 31 9901 8632',
                'logo' => 'images/logo-janur-nobg.png',
            ]
        );

        foreach ([
            ['pelabuhan_asal' => 'Surabaya', 'pelabuhan_tujuan' => 'Sorong', 'harga_dasar' => 8500],
            ['pelabuhan_asal' => 'Surabaya', 'pelabuhan_tujuan' => 'Banjarmasin', 'harga_dasar' => 5000],
            ['pelabuhan_asal' => 'Jakarta', 'pelabuhan_tujuan' => 'Makassar', 'harga_dasar' => 7000],
        ] as $rute) {
            RuteHarga::updateOrCreate(
                [
                    'pelabuhan_asal' => $rute['pelabuhan_asal'],
                    'pelabuhan_tujuan' => $rute['pelabuhan_tujuan'],
                ],
                $rute
            );
        }
    }
}
