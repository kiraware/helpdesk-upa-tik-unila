<?php

namespace Database\Seeders;

use App\Models\Department;
use Illuminate\Database\Seeder;

class DepartmentSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $departments = [
            'Fakultas Ekonomi dan Bisnis',
            'Fakultas Hukum',
            'Fakultas Ilmu Sosial dan Ilmu Politik',
            'Fakultas Kedokteran',
            'Fakultas Keguruan dan Ilmu Pendidikan',
            'Fakultas Matematika dan Ilmu Pengetahuan Alam',
            'Fakultas Pertanian',
            'Fakultas Teknik',
            'Pascasarjana',
            'BAK',
            'BUK',
            'BPHM',
            'BPU',
            'LPPM',
            'LP3M',
            'UPA Perpustakaan',
            'UPA TIK',
            'UPA LTSIT',
            'UPA Bahasa',
            'UPA PKK',
            'UPA PKLI',
            'UPA Kearsipan',
            'Lainnya',
        ];

        foreach ($departments as $departmentName) {
            Department::firstOrCreate([
                'name' => $departmentName,
            ]);
        }
    }
}
