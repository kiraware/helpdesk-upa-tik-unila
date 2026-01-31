<?php

use App\Enums\UserRole;
use App\Http\Controllers\ConfigurationController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\DivisionController;
use App\Http\Controllers\GuestTicketCommentController;
use App\Http\Controllers\GuestTicketController;
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
Route::get('/test-login/{role?}', function (?string $role = null) {
    // 1. Jika Role tidak diisi, Tampilkan Pilihan Menu
    if (! $role) {
        $roles = \App\Enums\UserRole::cases();

        $html = '<div style="display:flex; flex-direction:column; align-items:center; justify-content:center; height:100vh; font-family:sans-serif; gap:1rem; background:#f3f4f6;">';
        $html .= '<h1 style="color:#1f2937;">Pilih User untuk Login</h1>';

        foreach ($roles as $r) {
            // Cek apakah user tersedia di DB
            $exists = \App\Models\User::where('role', $r->value)->exists();
            $color = $exists ? '#3b82f6' : '#9ca3af'; // Biru jika ada, Abu jika tidak ada
            $cursor = $exists ? 'pointer' : 'not-allowed';
            $link = $exists ? url("/test-login/{$r->value}") : '#';
            $text = $exists ? 'Login as '.strtoupper($r->value) : strtoupper($r->value).' (User not found)';

            $html .= "<a href='{$link}' style='text-decoration:none; background:{$color}; color:white; padding:10px 20px; border-radius:5px; width:250px; text-align:center; cursor:{$cursor}'>{$text}</a>";
        }

        $html .= '<p style="color:#6b7280; margin-top:20px; font-size:12px;">Pastikan Anda sudah menjalankan database seeder.</p>';
        $html .= '</div>';

        return $html;
    }

    // 2. Jika Role diisi, Cari User dan Login
    $user = \App\Models\User::where('role', $role)->first();

    if ($user) {
        // Logout user sebelumnya (opsional, untuk kebersihan sesi)
        \Illuminate\Support\Facades\Auth::logout();

        // Login user baru
        \Illuminate\Support\Facades\Auth::login($user);

        // Regenerate session ID (security best practice, meski testing)
        request()->session()->regenerate();

        return redirect()->route('dashboard');
    }

    return "User dengan role '{$role}' tidak ditemukan di database. Silakan jalankan seeder.";
});

Route::controller(GuestTicketController::class)->group(function () {
    Route::get('/tracking', 'index')->name('guest.tracking.index');
    Route::post('/tracking/search', 'search')->name('guest.tracking.search');
    Route::get('/create-ticket', 'create')->name('guest.tickets.create');
    Route::post('/create-ticket', 'store')->name('guest.tickets.store');
    Route::get('/tracking/{ticket:ticket_code}', 'show')->name('guest.tracking.show');

    Route::post('/guest/upload-trix', 'storeEmbeddedFile')->name('guest.upload.editor.trix');
});

Route::controller(GuestTicketCommentController::class)->group(function () {
    Route::post('/guest-tickets/{ticket}/comments', 'store')->name('guest.tickets.comments.store');
    Route::post('/guest/upload-editor-attachments', 'storeEmbeddedFile')->name('guest.comments.upload.editor.attachments');
});

Route::middleware(['auth'])->group(function () {

    // 1. DASHBOARD (Semua Role punya dashboard, logic tampilan diatur di Controller)
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');

    // 2. TICKETS (Semua Role butuh akses tiket)
    // User: Create & View Own. Admin/Super: View All & Manage.
    Route::resource('tickets', TicketController::class);

    Route::post('/tickets/{ticket}/comments', [TicketCommentController::class, 'store'])
        ->name('tickets.comments.store');
    Route::post('/comments/upload-editor-attachments', [TicketCommentController::class, 'storeEmbeddedFile'])
        ->name('comments.upload.editor.attachments');

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

        // Close Ticket Logic
        Route::patch('/tickets/{ticket}/close', [TicketController::class, 'close'])
            ->name('tickets.close');

        // Laporan (Reports)
        Route::get('/reports', [ReportController::class, 'index'])->name('reports.index');

        // Print Assignment Letter
        Route::get('/tickets/{ticket}/assignment', [TicketController::class, 'printAssignment'])
            ->name('tickets.print_assignment');

        // Configuration Management
        Route::get('/configurations', [ConfigurationController::class, 'index'])->name('configurations.index');
        Route::put('/configurations', [ConfigurationController::class, 'update'])->name('configurations.update');
    });

    // 4. GROUP SUPERUSER ONLY
    Route::middleware([
        'role:'.UserRole::SUPERUSER->value,
    ])->group(function () {
        // Manajemen User (Admin & Superuser lain)
        Route::resource('users', UserController::class);
    });
});
