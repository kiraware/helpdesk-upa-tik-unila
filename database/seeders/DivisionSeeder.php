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
            'Pusat Data dan Informasi',
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
