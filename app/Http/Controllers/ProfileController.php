<?php

namespace App\Http\Controllers;

use App\Enums\UserRole;
use App\Models\Department;
use App\Models\Division;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class ProfileController extends Controller
{
    /**
     * Menampilkan halaman profil.
     */
    public function edit(Request $request)
    {
        $user = $request->user();

        // Ambil data berdasarkan role
        $isAdminOrSuperuser = in_array($user->role, [UserRole::ADMIN, UserRole::SUPERUSER]);

        $divisions = $isAdminOrSuperuser ? Division::all() : collect();
        $departments = $user->role === UserRole::USER ? Department::all() : collect();

        return view('profile.edit', compact('user', 'divisions', 'departments', 'isAdminOrSuperuser'));
    }

    /**
     * Memperbarui informasi profil.
     */
    public function update(Request $request)
    {
        $user = $request->user();
        $isAdminOrSuperuser = in_array($user->role, [UserRole::ADMIN, UserRole::SUPERUSER]);

        // Aturan validasi dinamis berdasarkan role
        $rules = [
            'phone' => ['nullable', 'string', 'max:20'],
            'avatar' => ['nullable', 'image', 'mimes:jpg,jpeg,png', 'max:2048'], // Max 2MB
        ];

        if ($isAdminOrSuperuser) {
            $rules['division_id'] = ['nullable', 'exists:divisions,id'];
        } else {
            $rules['department_id'] = ['nullable', 'exists:departments,id'];
        }

        $validated = $request->validate($rules);

        // Handle upload avatar
        if ($request->hasFile('avatar')) {
            // Hapus file lama jika ada
            if ($user->avatar_path && Storage::disk('public')->exists($user->avatar_path)) {
                Storage::disk('public')->delete($user->avatar_path);
            }

            // Simpan file baru
            $path = $request->file('avatar')->store('avatars', 'public');
            $user->avatar_path = $path;
        }

        // Update nomor telepon
        $user->phone = $validated['phone'] ?? $user->phone;

        // Update relasi (Penanggung Jawab atau Departemen)
        if ($isAdminOrSuperuser && array_key_exists('division_id', $validated)) {
            $user->division_id = $validated['division_id'];
        } elseif ($user->role === UserRole::USER && array_key_exists('department_id', $validated)) {
            $user->department_id = $validated['department_id'];
        }

        $user->save();

        return back()->with('success', 'Profil berhasil diperbarui.');
    }

    /**
     * Menghapus foto profil beserta file fisiknya.
     */
    public function destroyAvatar(Request $request)
    {
        $user = $request->user();

        if ($user->avatar_path && Storage::disk('public')->exists($user->avatar_path)) {
            Storage::disk('public')->delete($user->avatar_path);

            $user->avatar_path = null;
            $user->save();

            return back()->with('success', 'Foto profil berhasil dihapus.');
        }

        return back()->with('warning', 'Tidak ada foto profil untuk dihapus.');
    }
}
