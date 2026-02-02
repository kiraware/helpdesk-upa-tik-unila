<?php

namespace Database\Seeders;

use App\Models\Service;
use Illuminate\Database\Seeder;

class ServiceSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $services = [
            'Lupa Password SSO',
            'Registrasi SSO',
            'Email Resmi Unila',
            'Jaringan Internet',
            'Website Down',
            'Sistem Informasi',
            'Siakadu',
            'Hosting dan Domain',
            'Tanda Tangan Elektronik',
            'Lainnya',
        ];

        foreach ($services as $serviceName) {
            Service::firstOrCreate(
                ['name' => $serviceName],
                ['is_active' => true]
            );
        }
    }
}
