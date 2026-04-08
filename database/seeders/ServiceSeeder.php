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
        $guestServices = [
            'Lupa Password SSO',
            'Registrasi SSO',
            'Email Resmi Unila',
        ];

        $userServices = [
            'Jaringan Internet',
            'Website Down',
            'Sistem Informasi',
            'Siakadu',
            'Hosting dan Domain',
            'Tanda Tangan Elektronik',
            'Lainnya',
        ];

        // Seed untuk guest
        foreach ($guestServices as $serviceName) {
            Service::firstOrCreate(
                ['name' => $serviceName],
                [
                    'is_active' => true,
                    'show_to_guest' => true,
                    'show_to_user' => false,
                ]
            );
        }

        // Seed untuk user
        foreach ($userServices as $serviceName) {
            Service::firstOrCreate(
                ['name' => $serviceName],
                [
                    'is_active' => true,
                    'show_to_guest' => false,
                    'show_to_user' => true,
                ]
            );
        }
    }
}
