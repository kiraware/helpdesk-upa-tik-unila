<?php

namespace App\Console\Commands;

use App\Enums\UserRole;
use App\Models\Division;
use App\Models\User;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Validator;

class CreateSuperUser extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'make:superuser';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Membuat user superuser atau admin secara interaktif';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('--- Setup Superuser / Admin ---');

        // Pertanyaan Field Wajib
        $name = $this->askWithValidation('Nama Lengkap', 'name', 'required|string|max:255');
        $username_sso = $this->askWithValidation('Username SSO', 'username_sso', 'required|string|max:255|unique:users');
        $email = $this->askWithValidation('Email', 'email', 'required|email|max:255|unique:users');

        // Pertanyaan Field Opsional
        $phone = $this->askWithValidation('No. Telepon (Kosongkan jika tidak ada)', 'phone', 'nullable|string|max:255', true);
        $identity_number = $this->askWithValidation('NIP / NIK (Kosongkan jika tidak ada)', 'identity_number', 'nullable|string|max:255', true);

        // Pertanyaan Role Akses (Default: superuser)
        $role = $this->choice(
            'Pilih Role Akses',
            [UserRole::SUPERUSER->value, UserRole::ADMIN->value],
            0 // Index 0 = superuser
        );

        // Pilihan Divisi jika ada data di tabel divisions
        $division_id = null;
        $divisions = Division::all();

        if ($divisions->isNotEmpty()) {
            $divisionChoices = ['0' => '-- Tidak ada divisi --'];
            foreach ($divisions as $div) {
                $divisionChoices[(string) $div->id] = $div->name;
            }

            $selectedDivName = $this->choice(
                'Pilih Divisi',
                array_values($divisionChoices),
                0
            );

            // Mencari ID dari nama divisi yang dipilih
            if ($selectedDivName !== '-- Tidak ada divisi --') {
                $division_id = array_search($selectedDivName, $divisionChoices);
            }
        } else {
            $this->line('<fg=yellow>Info:</> Tabel divisions masih kosong, field divisi di-skip.');
        }

        $this->info('Menyimpan data user...');

        // Insert ke database
        User::create([
            'name' => $name,
            'username_sso' => $username_sso,
            'email' => $email,
            'phone' => $phone,
            'identity_number' => $identity_number,
            'role' => $role,
            'division_id' => $division_id,
        ]);

        $this->info("User [$name] dengan role [$role] berhasil dibuat!");
    }

    /**
     * Helper function untuk looping pertanyaan jika validasi gagal.
     */
    protected function askWithValidation($question, $field, $rules, $isNullable = false)
    {
        $value = $this->ask($question);

        if ($isNullable && empty($value)) {
            return null;
        }

        $validator = Validator::make([$field => $value], [
            $field => $rules,
        ]);

        if ($validator->fails()) {
            $this->error($validator->errors()->first($field));

            // Tanya ulang jika salah
            return $this->askWithValidation($question, $field, $rules, $isNullable);
        }

        return $value;
    }
}
