<?php

namespace Database\Seeders;

use App\Models\Configuration;
use Illuminate\Database\Seeder;

class ConfigurationSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        Configuration::updateOrCreate(
            ['id' => 1],
            [
                'upa_head_name' => 'Dr. Eng. Mardiana, S.T., M.T.',
                'upa_head_nip' => '19720316 199903 2 003',
                'upa_head_position' => 'Kepala UPA TIK',
            ]
        );
    }
}
