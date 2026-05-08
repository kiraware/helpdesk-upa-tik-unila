<?php

namespace Database\Seeders;

use App\Models\Division;
use Illuminate\Database\Seeder;

class DivisionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $divisions = [
            'Pengembangan dan Inovasi Teknologi Informasi',
            'Layanan Sistem dan Teknologi Informasi',
            'Manajemen dan Integrasi Sistem Teknologi Informasi',
            'Infrastruktur Jaringan',
            'Sumber Daya Sistem Informasi',
            'Pusat Data dan Keamanan Informasi',
        ];

        foreach ($divisions as $divisionName) {
            Division::firstOrCreate([
                'name' => $divisionName,
            ]);
        }
    }
}
