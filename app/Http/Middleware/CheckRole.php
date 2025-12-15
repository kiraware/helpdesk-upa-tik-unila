<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class CheckRole
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next, string ...$roles): Response
    {
        // 1. Cek apakah user sudah login
        if (! Auth::check()) {
            return redirect('/login');
        }

        // 2. Ambil role user saat ini
        $userRole = Auth::user()->role;

        // 3. Cek apakah role user ada di dalam daftar yang dibolehkan
        // Logic ini perlu disesuaikan jika $userRole di database Anda berbentuk Object Enum
        // Jika Enum, gunakan: $userRole->value
        $roleValue = is_object($userRole) ? $userRole->value : $userRole;

        if (in_array($roleValue, $roles)) {
            return $next($request);
        }

        // 4. Jika tidak cocok, tampilkan error 403 (Forbidden)
        abort(403, 'Anda tidak memiliki akses ke halaman ini.');
    }
}
