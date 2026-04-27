<?php

namespace Database\Seeders;

use App\Enums\UserRole;
use App\Models\Division;
use App\Models\User;
use Illuminate\Database\Seeder;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        User::create([
            'name' => 'Super Administrator',
            'username_sso' => 'superadmin',
            'email' => 'superadmin@unila.ac.id',
            'role' => UserRole::SUPERUSER,
            'identity_number' => '000000001',
        ]);

        // 2. Buat Akun Staff Helpdesk (Pusat Data dan Informasi)
        // Pastikan DivisionSeeder dijalankan DULUAN agar divisi 'Pusat Data dan Informasi' ada
        $pusdatin = Division::where('name', 'Pusat Data dan Informasi')->first();

        if ($pusdatin) {
            User::create([
                'name' => 'Staff Pusat Data dan Informasi',
                'username_sso' => 'staff.pusdatin',
                'email' => 'helpdesk@unila.ac.id',
                'role' => UserRole::ADMIN,
                'division_id' => $pusdatin->id,
                'identity_number' => '198501012010121001',
            ]);
        }

        // 3. Buat 10 User Dummy (Mahasiswa/Dosen)
        User::factory(10)->create();

        // 4. Buat 3 Staff Dummy Lainnya
        User::factory(3)->admin()->create();
    }
}
