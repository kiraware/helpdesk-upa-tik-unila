<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DivisionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $divisions = [
            'Pusdatin',
            'PSI',
            'INF',
            'PPSI',
            'SDI',
        ];

        foreach ($divisions as $divisionName) {
            Division::firstOrCreate([
                'name' => $divisionName,
            ]);
        }
    }
}
