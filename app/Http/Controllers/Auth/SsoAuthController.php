<?php

namespace App\Http\Controllers\Auth;

use App\Enums\UserEntity;
use App\Enums\UserRole;
use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Http;

class SsoAuthController extends Controller
{
    public function showLoginForm()
    {
        return view('auth.login');
    }

    public function login(Request $request)
    {
        $request->validate([
            'username' => 'required|string',
            'password' => 'required|string',
        ]);

        $response = Http::post('http://localhost:3000/login', [
            'username' => $request->username,
            'password' => $request->password,
        ]);

        if ($response->successful()) {
            $data = $response->json();
            $ssoUser = $data['user'];

            $user = User::where('username_sso', $ssoUser['username'])->first();

            if (! $user) {
                $user = new User;
                $user->username_sso = $ssoUser['username'];
                $user->role = UserRole::USER;
            }

            /**
             * LOGIKA: "Jika data dari API SSO kosong, biarkan data yang sudah ada"
             * Menggunakan operator ?: (elvis) akan mengambil nilai kiri jika TRUE (tidak kosong/null),
             * jika FALSE (kosong/null), akan mengambil nilai kanan (nilai lama di DB).
             */
            $user->identity_number = ($ssoUser['numberID'] ?? null) ?: $user->identity_number;
            $user->name = ($ssoUser['name'] ?? null) ?: $user->name;
            $user->email = ($ssoUser['email'] ?? null) ?: $user->email;
            $user->phone = ($ssoUser['phone'] ?? null) ?: $user->phone;

            $rawEntity = $ssoUser['status'] ?? '';

            $normalizedEntity = strtolower(trim($rawEntity));

            $user->entity = match ($normalizedEntity) {
                'super user', 'superuser' => UserEntity::SUPER_USER,
                'mahasiswa' => UserEntity::MAHASISWA,
                'dosen' => UserEntity::DOSEN,
                'karyawan', 'staff' => UserEntity::KARYAWAN, // Bisa tangkap lebih dari 1 variasi kata
                'tamu', 'guest' => UserEntity::TAMU,
                default => UserEntity::LAINNYA,  // Jika null, kosong, atau tidak dikenali, otomatis jadi 'Lainnya'
            };

            $user->save();

            Auth::login($user);

            $request->session()->regenerate();
            session(['sso_token' => $data['token']]);

            return redirect()->intended('/dashboard');
        }

        return back()->withErrors([
            'username' => 'Username atau password salah.',
        ])->withInput($request->only('username'));
    }

    public function logout(Request $request)
    {
        Auth::logout();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect('/');
    }
}
