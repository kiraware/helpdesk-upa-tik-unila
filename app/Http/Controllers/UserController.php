<?php

namespace App\Http\Controllers;

use App\Enums\UserRole;
use App\Models\Division;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Validation\Rules\Enum;

class UserController extends Controller
{
    public function index(Request $request)
    {
        $query = User::whereIn('role', [UserRole::ADMIN->value, UserRole::SUPERUSER->value])
            ->with('division')
            ->orderBy('name', 'asc');

        if ($request->filled('q')) {
            $query->where(function ($q) use ($request) {
                $q->where('name', 'like', '%'.$request->q.'%')
                    ->orWhere('username_sso', 'like', '%'.$request->q.'%')
                    ->orWhere('email', 'like', '%'.$request->q.'%')
                    ->orWhere('phone', 'like', '%'.$request->q.'%');
            });
        }

        $users = $query->paginate(10)->withQueryString();
        $divisions = Division::all();

        return view('users.index', compact('users', 'divisions'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'username_sso' => 'required|string|max:255',
            'phone' => 'nullable|string|max:255',
            'role' => ['required', new Enum(UserRole::class)],
            'division_id' => 'nullable|exists:divisions,id',
        ]);

        // Cek apakah user sudah ada di database
        $user = User::where('username_sso', $request->username_sso)->first();

        if ($user) {
            // Jika user sudah ada, cukup perbarui role dan divisinya
            $user->update([
                'role' => $request->role,
                'division_id' => $request->division_id,
                // Jika input phone diisi, gunakan yang baru. Jika tidak, pertahankan yang lama
                'phone' => $request->phone ?? $user->phone,
            ]);

            return back()->with('success', 'Hak akses staff berhasil ditambahkan pada user yang sudah ada.');
        }

        // Jika user benar-benar belum ada, buat record baru
        User::create([
            'username_sso' => $request->username_sso,
            'name' => $request->username_sso, // Fallback name sebelum tersinkronisasi SSO
            'phone' => $request->phone,
            'role' => $request->role,
            'division_id' => $request->division_id,
        ]);

        return back()->with('success', 'Staff baru berhasil ditambahkan. Data profil akan sinkron saat user login.');
    }

    public function update(Request $request, User $user)
    {
        $request->validate([
            'username_sso' => 'required|string|max:255|unique:users,username_sso,'.$user->id,
            'phone' => 'nullable|string|max:255',
            'role' => ['required', new Enum(UserRole::class)],
            'division_id' => 'nullable|exists:divisions,id',
        ]);

        $user->update($request->only('username_sso', 'phone', 'role', 'division_id'));

        return back()->with('success', 'Data staff berhasil diperbarui.');
    }

    public function destroy(User $user)
    {
        $user->delete();

        return back()->with('success', 'Staff berhasil dihapus.');
    }
}
