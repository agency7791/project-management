<?php

use App\Http\Controllers\ProfileController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\ClientController;
use App\Http\Controllers\ProjectController;
use App\Http\Controllers\TeamController;
use App\Http\Controllers\TimeEntryController;
use App\Http\Controllers\ChatController;
use App\Http\Controllers\ReportController;
use Illuminate\Foundation\Application;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;

Route::get('/', function () {
    return Inertia::render('Welcome', [
        'canLogin' => Route::has('login'),
        'canRegister' => Route::has('register'),
        'laravelVersion' => Application::VERSION,
        'phpVersion' => PHP_VERSION,
    ]);
});

Route::middleware(['auth', 'verified'])->group(function () {
    // Dashboard
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');
    
    // Profile
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
    
    // Clients (Admin and Manager only)
    Route::middleware('can:manage-projects')->group(function () {
        Route::resource('clients', ClientController::class);
    });
    
    // Projects
    Route::resource('projects', ProjectController::class);
    Route::post('projects/{project}/assign-team', [ProjectController::class, 'assignTeam'])->name('projects.assign-team');
    
    // Teams
    Route::resource('teams', TeamController::class);
    Route::post('teams/{team}/add-member', [TeamController::class, 'addMember'])->name('teams.add-member');
    Route::delete('teams/{team}/remove-member/{user}', [TeamController::class, 'removeMember'])->name('teams.remove-member');
    
    // Time Entries
    Route::resource('time-entries', TimeEntryController::class);
    Route::post('time-entries/start-timer', [TimeEntryController::class, 'startTimer'])->name('time-entries.start-timer');
    Route::post('time-entries/stop-timer', [TimeEntryController::class, 'stopTimer'])->name('time-entries.stop-timer');
    
    // Chat
    Route::get('chat', [ChatController::class, 'index'])->name('chat.index');
    Route::get('chat/rooms/{room}', [ChatController::class, 'show'])->name('chat.show');
    Route::post('chat/rooms/{room}/messages', [ChatController::class, 'sendMessage'])->name('chat.send-message');
    Route::post('chat/rooms/{room}/upload', [ChatController::class, 'uploadFile'])->name('chat.upload-file');
    
    // Reports (Admin and Manager only)
    Route::middleware('can:manage-projects')->group(function () {
        Route::get('reports', [ReportController::class, 'index'])->name('reports.index');
        Route::get('reports/timesheet', [ReportController::class, 'timesheet'])->name('reports.timesheet');
        Route::get('reports/timesheet/export', [ReportController::class, 'exportTimesheet'])->name('reports.timesheet.export');
        Route::get('reports/project-summary', [ReportController::class, 'projectSummary'])->name('reports.project-summary');
    });
    
    // File Downloads
    Route::get('files/{file}/download', function(\App\Models\ProjectFile $file) {
        return response()->download(storage_path('app/' . $file->file_path), $file->original_name);
    })->name('files.download');
});

require __DIR__.'/auth.php';
