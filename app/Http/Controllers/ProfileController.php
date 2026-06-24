<?php

namespace App\Http\Controllers;

use App\Enums\UserRole;
use App\Helpers\ImageSanitizer;
use App\Models\Department;
use App\Models\Division;
use App\Rules\SafeFile;
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

        $isAdminOrSuperuser = in_array($user->role, [UserRole::ADMIN, UserRole::SUPERUSER]);

        $divisions = $isAdminOrSuperuser ? Division::orderByRaw("CASE WHEN LOWER(name) = 'lainnya' THEN 1 ELSE 0 END ASC, LOWER(name) ASC")->get() : collect();
        $departments = $user->role === UserRole::USER ? Department::orderByRaw("CASE WHEN LOWER(name) = 'lainnya' THEN 1 ELSE 0 END ASC, LOWER(name) ASC")->get() : collect();

        return view('profile.edit', compact('user', 'divisions', 'departments', 'isAdminOrSuperuser'));
    }

    /**
     * Memperbarui informasi profil.
     */
    public function update(Request $request)
    {
        $user = $request->user();
        $isAdminOrSuperuser = in_array($user->role, [UserRole::ADMIN, UserRole::SUPERUSER]);

        $rules = [
            'phone' => ['nullable', 'string', 'regex:/^[0-9]+$/', 'max:20'],
            'avatar' => ['nullable', 'image', 'mimes:jpg,jpeg,png', 'max:2048', new SafeFile], // Max 2MB
        ];

        if ($isAdminOrSuperuser) {
            $rules['division_id'] = ['nullable', 'exists:divisions,id'];
        } else {
            $rules['department_id'] = ['nullable', 'exists:departments,id'];
        }

        $validated = $request->validate($rules);

        if ($request->hasFile('avatar')) {
            if ($user->avatar_path && Storage::disk('public')->exists($user->avatar_path)) {
                Storage::disk('public')->delete($user->avatar_path);
            }

            $path = $request->file('avatar')->store('avatars', 'public');

            ImageSanitizer::sanitize(storage_path('app/public/'.$path), $request->file('avatar')->getClientOriginalExtension());

            $user->avatar_path = $path;
        }

        $user->phone = $validated['phone'] ?? $user->phone;

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
