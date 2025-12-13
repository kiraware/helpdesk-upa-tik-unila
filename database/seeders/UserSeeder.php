<?php

namespace Database\Seeders;

use App\Enums\UserRole;
use App\Models\Division;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

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
            'password' => Hash::make('password'),
            'role' => UserRole::SUPERUSER,
            'identity_number' => '000000001',
        ]);

        // 2. Buat Akun Staff Helpdesk (Pusdatin)
        // Pastikan DivisionSeeder dijalankan DULUAN agar divisi 'Pusdatin' ada
        $pusdatin = Division::where('name', 'Pusdatin')->first();

        if ($pusdatin) {
            User::create([
                'name' => 'Staff Pusdatin',
                'username_sso' => 'staff.pusdatin',
                'email' => 'helpdesk@unila.ac.id',
                'password' => Hash::make('password'),
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
