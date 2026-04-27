<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\Http;

class SsoUserController extends Controller
{
    /**
     * Menampilkan daftar User SSO dari API
     */
    public function index(Request $request)
    {
        $search = $request->input('search', '');
        $page = $request->input('page', 1);
        $limit = 10;
        $token = session('sso_token');

        if (! $token) {
            return redirect()->route('login')->withErrors(['username' => 'Sesi SSO telah berakhir. Silakan login kembali.']);
        }

        // Call API Listing User Express.js
        $response = Http::withToken($token)->get('http://localhost:3000/users', [
            'search' => $search,
            'page' => $page,
            'limit' => $limit,
        ]);

        if ($response->successful()) {
            $ssoData = $response->json();

            // Konversi Array dari API menjadi Objek Paginator Laravel
            $users = new LengthAwarePaginator(
                $ssoData['data'] ?? [],          // Kumpulan data pada halaman ini
                $ssoData['totalData'] ?? 0,      // Total keseluruhan data
                $limit,                          // Jumlah per halaman
                $ssoData['currentPage'] ?? 1,    // Halaman aktif
                [
                    'path' => $request->url(),   // URL dasar untuk link
                    'query' => $request->query(), // Mempertahankan parameter 'search' di link paginasi
                ]
            );

            return view('sso-users.index', compact('users', 'search'));
        }

        if ($response->status() === 401 || $response->status() === 403) {
            return redirect()->route('login')->withErrors(['username' => 'Token SSO tidak valid atau kedaluwarsa. Silakan login kembali.']);
        }

        return back()->with('error', 'Gagal mengambil data dari server SSO.');
    }

    /**
     * Memproses reset password User SSO
     */
    public function resetPassword(Request $request)
    {
        $request->validate([
            'username' => 'required|string',
            'new_password' => 'required|string|min:6',
        ]);

        // Call API Reset Password Express.js
        $response = Http::post('http://localhost:3000/reset-password', [
            'username' => $request->username,
            'newPassword' => $request->new_password,
        ]);

        if ($response->successful()) {
            return back()->with('success', "Password untuk user '{$request->username}' berhasil di-reset.");
        }

        return back()->with('error', 'Gagal mereset password. Pastikan username benar.');
    }
}
