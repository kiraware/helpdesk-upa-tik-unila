<?php

use App\Enums\UserRole;
use App\Http\Controllers\DivisionController;
use App\Http\Controllers\ServiceController;
use App\Http\Controllers\TicketCommentController;
use App\Http\Controllers\TicketController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});

Route::middleware([
    'auth',
    'role:'.UserRole::ADMIN->value.','.UserRole::SUPERUSER->value,
])->group(function () {
    Route::resource('services', ServiceController::class)
        ->only(['index', 'store', 'update', 'destroy']);
    Route::resource('divisions', DivisionController::class)
        ->only(['index', 'store', 'update', 'destroy']);
    Route::resource('tickets', TicketController::class)
        ->only(['index', 'show', 'store', 'update']);
    Route::post('/tickets/{ticket}/assign-me', [TicketController::class, 'assignMe'])
        ->name('tickets.assign.me');
    Route::post('/tickets/{ticket}/comments', [TicketCommentController::class, 'store'])
        ->name('tickets.comments.store');
    Route::post('/comments/upload-editor-image', [TicketCommentController::class, 'uploadEditorImage'])
        ->name('comments.upload.editor.image');
});

Route::get('/test-login', function () {
    $user = \App\Models\User::first();
    if ($user) {
        \Illuminate\Support\Facades\Auth::login($user);

        return redirect('/services');
    }

    return 'Tidak ada user di database, jalankan seeder dulu.';
});
