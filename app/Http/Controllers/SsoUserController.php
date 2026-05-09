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

            $users = new LengthAwarePaginator(
                $ssoData['data'] ?? [],
                $ssoData['totalData'] ?? 0,
                $limit,
                $ssoData['currentPage'] ?? 1,
                [
                    'path' => $request->url(),
                    'query' => $request->query(),
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
     * Menambahkan User SSO Baru
     */
    public function store(Request $request)
    {
        $request->validate([
            'numberID' => 'required|string',
            'name' => 'required|string',
            'username' => 'required|string',
            'domain_email' => 'required|string',
            'status' => 'required|string',
            'tgl_lahir' => 'nullable|date',
            'fakultas' => 'nullable|string',
            'jurusan' => 'nullable|string',
            'unit_kerja' => 'nullable|string',
            'no_telp' => 'nullable|string',
            'alamat' => 'nullable|string',
        ]);

        $token = session('sso_token');

        if (! $token) {
            return redirect()->route('login')->withErrors(['username' => 'Sesi SSO telah berakhir. Silakan login kembali.']);
        }

        $response = Http::withToken($token)->post('http://localhost:3000/users', $request->all());

        if ($response->successful()) {
            return back()->with('success', 'User SSO berhasil ditambahkan.');
        }

        $errorMessage = $response->json('message') ?? 'Gagal menambahkan user SSO.';

        return back()->with('error', $errorMessage);
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
            return back()->with('success', "Password untuk user {$request->username} berhasil di-reset.");
        }

        return back()->with('error', 'Gagal mereset password. Pastikan username benar.');
    }

    /**
     * Menonaktifkan User SSO
     */
    public function inactive(Request $request)
    {
        $request->validate([
            'username' => 'required|string',
        ]);

        $token = session('sso_token');

        if (! $token) {
            return redirect()->route('login')->withErrors(['username' => 'Sesi SSO telah berakhir. Silakan login kembali.']);
        }

        $response = Http::withToken($token)->post('http://localhost:3000/inactive-user', [
            'username' => $request->username,
        ]);

        if ($response->successful()) {
            return back()->with('success', "User '{$request->username}' berhasil dinonaktifkan.");
        }

        $errorMessage = $response->json('message') ?? 'Gagal menonaktifkan user SSO.';

        return back()->with('error', $errorMessage);
    }
}
