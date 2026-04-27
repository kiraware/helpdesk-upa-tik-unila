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
            'name' => 'required|string|max:255',
            'username_sso' => 'required|string|max:255|unique:users',
            'email' => 'required|email|max:255|unique:users',
            'phone' => 'nullable|string|max:255',
            'identity_number' => 'nullable|string|max:255',
            'role' => ['required', new Enum(UserRole::class)],
            'division_id' => 'nullable|exists:divisions,id',
        ]);

        User::create([
            'name' => $request->name,
            'username_sso' => $request->username_sso,
            'email' => $request->email,
            'phone' => $request->phone,
            'identity_number' => $request->identity_number,
            'role' => $request->role,
            'division_id' => $request->division_id,
        ]);

        return back()->with('success', 'Staff berhasil ditambahkan.');
    }

    public function update(Request $request, User $user)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'username_sso' => 'required|string|max:255|unique:users,username_sso,'.$user->id,
            'email' => 'required|email|max:255|unique:users,email,'.$user->id,
            'phone' => 'nullable|string|max:255',
            'identity_number' => 'nullable|string|max:255',
            'role' => ['required', new Enum(UserRole::class)],
            'division_id' => 'nullable|exists:divisions,id',
        ]);

        $user->update($request->only('name', 'username_sso', 'email', 'phone', 'identity_number', 'role', 'division_id'));

        return back()->with('success', 'Staff berhasil diperbarui.');
    }

    public function destroy(User $user)
    {
        $user->delete();

        return back()->with('success', 'Staff berhasil dihapus.');
    }
}
