<?php

use App\Enums\TicketStatus;
use App\Enums\UserRole;
use App\Http\Controllers\Auth\SsoAuthController;
use App\Http\Controllers\ConfigurationController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\DepartmentController;
use App\Http\Controllers\DivisionController;
use App\Http\Controllers\GuestTicketCommentController;
use App\Http\Controllers\GuestTicketController;
use App\Http\Controllers\NotificationController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\ReportController;
use App\Http\Controllers\ServiceController;
use App\Http\Controllers\SsoUserController;
use App\Http\Controllers\SurveyQuestionController;
use App\Http\Controllers\TicketCommentController;
use App\Http\Controllers\TicketController;
use App\Http\Controllers\TicketSurveyController;
use App\Http\Controllers\UserController;
use App\Http\Middleware\EnsureSurveyCompleted;
use App\Models\Ticket;
use Illuminate\Support\Facades\Route;

Route::get('/sitemap.xml', function () {
    $urls = [
        ['loc' => url('/'),              'priority' => '1.0', 'changefreq' => 'monthly'],
        ['loc' => url('/faq'),           'priority' => '0.8', 'changefreq' => 'monthly'],
        ['loc' => url('/tracking'),      'priority' => '0.6', 'changefreq' => 'yearly'],
        ['loc' => url('/create-ticket'), 'priority' => '0.7', 'changefreq' => 'yearly'],
    ];

    return response()
        ->view('sitemap', ['urls' => $urls])
        ->header('Content-Type', 'application/xml');
})->name('sitemap');

Route::get('/', function () {
    return view('welcome');
});

Route::view('/faq', 'faq')->name('faq');

Route::middleware('guest')->group(function () {
    Route::get('/login', [SsoAuthController::class, 'showLoginForm'])->name('login');
    Route::post('/login', [SsoAuthController::class, 'login']);
});

Route::post('/logout', [SsoAuthController::class, 'logout'])->name('logout')->middleware('auth');

Route::controller(GuestTicketController::class)->group(function () {
    Route::get('/tracking', 'index')->name('guest.tracking.index');
    Route::post('/tracking/search', 'search')->name('guest.tracking.search');
    Route::get('/create-ticket', 'create')->name('guest.tickets.create');
    Route::post('/create-ticket', 'store')->name('guest.tickets.store');
    Route::get('/tracking/{ticket:ticket_code}', 'show')->name('guest.tracking.show');

    Route::post('/guest/tickets/upload-attachment', 'storeEmbeddedFile')->name('guest.tickets.upload.attachment');
});

Route::controller(GuestTicketCommentController::class)->group(function () {
    Route::post('/guest-tickets/{ticket}/comments', 'store')->name('guest.tickets.comments.store');
    Route::post('/guest/comments/upload-attachment', 'storeEmbeddedFile')->name('guest.comments.upload.attachments');
});

Route::post('/tickets/{ticket}/survey', [TicketSurveyController::class, 'store'])->name('tickets.survey.store');

Route::middleware(['auth', EnsureSurveyCompleted::class])->group(function () {
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');
    Route::post('/tickets/upload-attachment', [TicketController::class, 'storeEmbeddedFile'])
        ->name('tickets.upload.attachment');
    Route::get('/tickets/waiting', [TicketController::class, 'waiting'])
        ->name('tickets.waiting');
    Route::get('/tickets/assigned', [TicketController::class, 'assigned'])
        ->name('tickets.assigned');
    Route::resource('tickets', TicketController::class)->except(['update']);

    Route::post('/tickets/{ticket}/comments', [TicketCommentController::class, 'store'])
        ->name('tickets.comments.store');
    Route::post('/comments/upload-attachments', [TicketCommentController::class, 'storeEmbeddedFile'])
        ->name('comments.upload.attachments');

    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile/avatar', [ProfileController::class, 'destroyAvatar'])->name('profile.avatar.destroy');

    Route::get('/notifications', [NotificationController::class, 'index'])
        ->name('notifications.index');
    Route::get('/notifications/{id}/read', [NotificationController::class, 'markAsReadAndRedirect'])
        ->name('notifications.read');
    Route::post('/notifications/mark-all', [NotificationController::class, 'markAllRead'])
        ->name('notifications.markAll');
    Route::get('/api/notifications', [NotificationController::class, 'fetchJson'])
        ->name('api.notifications');

    Route::middleware([
        'role:'.UserRole::ADMIN->value.','.UserRole::SUPERUSER->value,
    ])->group(function () {

        Route::get('/api/ticket-counts', function () {
            $user = auth()->user();

            return response()->json([
                'waitingCount' => Ticket::where('status', TicketStatus::WAITING)->count(),
                'assignedProgressCount' => Ticket::where('assigned_to', $user->id)
                    ->where('status', TicketStatus::PROGRESS)
                    ->count(),
            ]);
        })->name('api.ticket.counts');

        Route::resource('services', ServiceController::class)->except(['show']);
        Route::resource('divisions', DivisionController::class)->except(['show']);
        Route::resource('departments', DepartmentController::class)->except(['show']);

        Route::post('/tickets/{ticket}/assign-me', [TicketController::class, 'assignMe'])
            ->name('tickets.assign.me');

        Route::patch('/tickets/{ticket}/assignee', [TicketController::class, 'updateAssignee'])
            ->name('tickets.update_assignee');

        Route::patch('/tickets/{ticket}/service', [TicketController::class, 'updateService'])
            ->name('tickets.update_service');

        Route::patch('/tickets/{ticket}/priority', [TicketController::class, 'updatePriority'])
            ->name('tickets.update_priority');

        Route::patch('/tickets/{ticket}/close', [TicketController::class, 'close'])
            ->name('tickets.close');

        Route::get('/sso-users', [SsoUserController::class, 'index'])->name('sso-users.index');
        Route::post('/sso-users', [SsoUserController::class, 'store'])->name('sso-users.store');
        Route::post('/sso-users/reset-password', [SsoUserController::class, 'resetPassword'])->name('sso-users.reset-password');
        Route::post('/sso-users/inactive', [SsoUserController::class, 'inactive'])->name('sso-users.inactive');

        Route::get('/reports', [ReportController::class, 'index'])->name('reports.index');
        Route::get('/reports/export', [ReportController::class, 'export'])->name('reports.export');

        Route::get('/tickets/{ticket}/assignment', [TicketController::class, 'printAssignment'])
            ->name('tickets.print_assignment');
    });

    Route::middleware([
        'role:'.UserRole::SUPERUSER->value,
    ])->group(function () {

        Route::resource('users', UserController::class);

        Route::resource('survey-questions', SurveyQuestionController::class)->except(['show', 'create', 'edit']);
        Route::patch('/survey-questions/{survey_question}/toggle', [SurveyQuestionController::class, 'toggleActive'])
            ->name('survey-questions.toggle');

        Route::get('/configurations', [ConfigurationController::class, 'index'])->name('configurations.index');
        Route::put('/configurations', [ConfigurationController::class, 'update'])->name('configurations.update');
    });
});
