<?php

use App\Enums\UserRole;
use App\Http\Controllers\ServiceController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});

Route::middleware([
    'auth',
    'role:'.UserRole::ADMIN->value.','.UserRole::SUPERUSER->value,
])->group(function () {
    Route::resource('services', ServiceController::class);
});

Route::get('/test-login', function () {
    $user = \App\Models\User::first();
    if ($user) {
        \Illuminate\Support\Facades\Auth::login($user);

        return redirect('/services');
    }

    return 'Tidak ada user di database, jalankan seeder dulu.';
});
