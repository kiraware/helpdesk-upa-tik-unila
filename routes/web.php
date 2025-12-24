<?php

use App\Enums\UserRole;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\DivisionController;
use App\Http\Controllers\ReportController;
use App\Http\Controllers\ServiceController;
use App\Http\Controllers\TicketCommentController;
use App\Http\Controllers\TicketController;
use App\Http\Controllers\UserController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});

// --- TESTING ONLY ---
Route::get('/test-login', function () {
    $user = \App\Models\User::first();
    if ($user) {
        \Illuminate\Support\Facades\Auth::login($user);

        return redirect()->route('dashboard');
    }

    return 'Tidak ada user di database, jalankan seeder dulu.';
});
// --------------------

Route::middleware(['auth'])->group(function () {

    // 1. DASHBOARD (Semua Role punya dashboard, logic tampilan diatur di Controller)
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');

    // 2. TICKETS (Semua Role butuh akses tiket)
    // User: Create & View Own. Admin/Super: View All & Manage.
    Route::resource('tickets', TicketController::class);

    Route::post('/tickets/{ticket}/comments', [TicketCommentController::class, 'store'])
        ->name('tickets.comments.store');
    Route::post('/comments/upload-editor-image', [TicketCommentController::class, 'uploadEditorImage'])
        ->name('comments.upload.editor.image');

    // 3. GROUP ADMIN & SUPERUSER
    Route::middleware([
        'role:'.UserRole::ADMIN->value.','.UserRole::SUPERUSER->value,
    ])->group(function () {

        // Master Data
        Route::resource('services', ServiceController::class)->except(['show']);
        Route::resource('divisions', DivisionController::class)->except(['show']);

        // Assign Ticket Logic
        Route::post('/tickets/{ticket}/assign-me', [TicketController::class, 'assignMe'])
            ->name('tickets.assign.me');

        // Laporan (Reports)
        Route::get('/reports', [ReportController::class, 'index'])->name('reports.index');
    });

    // 4. GROUP SUPERUSER ONLY
    Route::middleware([
        'role:'.UserRole::SUPERUSER->value,
    ])->group(function () {
        // Manajemen User (Admin & Superuser lain)
        Route::resource('users', UserController::class);
    });
});
