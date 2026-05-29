<?php

namespace App\Console\Commands;

use App\Enums\UserRole;
use App\Models\Division;
use App\Models\User;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Validator;

class CreateStaff extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'make:staff';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Membuat user staff secara interaktif';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('--- Tambah/Update Staff (Admin/Superuser) ---');

        $username_sso = $this->askWithValidation('Username SSO', 'username_sso', 'required|string|max:255');

        $roleOptions = [UserRole::ADMIN->value, UserRole::SUPERUSER->value];
        $role = $this->choice('Pilih Role', $roleOptions, 0);

        $division_id = null;
        $divisions = Division::all();
        if ($divisions->count() > 0) {
            $divChoices = $divisions->pluck('name', 'id')->toArray();
            $divChoices['none'] = '-- Tidak ada penanggung jawab --';

            $selectedDiv = $this->choice('Pilih Penanggung Jawab', array_values($divChoices), count($divChoices) - 1);

            if ($selectedDiv !== '-- Tidak ada penanggung jawab --') {
                $division_id = $divisions->where('name', $selectedDiv)->first()->id;
            }
        }

        $user = User::where('username_sso', $username_sso)->first();

        if ($user) {
            $user->update([
                'role' => $role,
                'division_id' => $division_id,
            ]);
            $this->info("Berhasil! Hak akses [$role] telah ditambahkan ke user [$username_sso] yang sudah ada di database.");
        } else {
            User::create([
                'username_sso' => $username_sso,
                'name' => $username_sso,
                'role' => $role,
                'division_id' => $division_id,
            ]);
            $this->info("Staff baru [$username_sso] berhasil dibuat! Data profil akan otomatis terisi saat yang bersangkutan login.");
        }
    }

    protected function askWithValidation($question, $field, $rules)
    {
        $value = $this->ask($question);
        $validator = Validator::make([$field => $value], [$field => $rules]);

        if ($validator->fails()) {
            $this->error($validator->errors()->first($field));

            return $this->askWithValidation($question, $field, $rules);
        }

        return $value;
    }
}
