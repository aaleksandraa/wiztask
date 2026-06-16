<?php

use App\Http\Controllers\Auth\AuthenticatedSessionController;
use App\Http\Controllers\AttachmentController;
use App\Http\Controllers\AttachmentUploadController;
use App\Http\Controllers\ReportExportController;
use App\Http\Controllers\Web\ClientController;
use App\Http\Controllers\Web\DashboardController;
use App\Http\Controllers\Web\ProjectController;
use App\Http\Controllers\Web\ReportController;
use App\Http\Controllers\Web\SettingsController;
use App\Http\Controllers\Web\TaskController;
use App\Http\Controllers\Web\TimeEntryController;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return Auth::check() ? redirect()->route('dashboard') : redirect()->route('login');
});

Route::middleware(['auth'])->group(function () {
    Route::get('dashboard', [DashboardController::class, 'index'])->name('dashboard');

    Route::get('klijenti', [ClientController::class, 'index'])->name('clients.index');
    Route::post('klijenti', [ClientController::class, 'store'])->name('clients.store');
    Route::get('klijenti/{client}', [ClientController::class, 'show'])->name('clients.show');
    Route::put('klijenti/{client}', [ClientController::class, 'update'])->name('clients.update');
    Route::delete('klijenti/{client}', [ClientController::class, 'destroy'])->name('clients.destroy');

    Route::get('projekti', [ProjectController::class, 'index'])->name('projects.index');
    Route::post('projekti', [ProjectController::class, 'store'])->name('projects.store');
    Route::get('projekti/{project}', [ProjectController::class, 'show'])->name('projects.show');
    Route::put('projekti/{project}', [ProjectController::class, 'update'])->name('projects.update');
    Route::delete('projekti/{project}', [ProjectController::class, 'destroy'])->name('projects.destroy');

    Route::get('taskovi', [TaskController::class, 'index'])->name('tasks.index');
    Route::post('taskovi', [TaskController::class, 'store'])->name('tasks.store');
    Route::get('taskovi/{task}', [TaskController::class, 'show'])->name('tasks.show');
    Route::put('taskovi/{task}', [TaskController::class, 'update'])->name('tasks.update');
    Route::delete('taskovi/{task}', [TaskController::class, 'destroy'])->name('tasks.destroy');
    Route::post('taskovi/{task}/duplicate', [TaskController::class, 'duplicate'])->name('tasks.duplicate');
    Route::post('taskovi/{task}/archive', [TaskController::class, 'toggleArchive'])->name('tasks.archive');
    Route::patch('taskovi/{task}/status', [TaskController::class, 'updateStatus'])->name('tasks.status');
    Route::patch('taskovi/{task}/payment-status', [TaskController::class, 'updatePaymentStatus'])->name('tasks.payment-status');
    Route::post('taskovi/{task}/vrijeme', [TaskController::class, 'storeTime'])->name('tasks.time.store');
    Route::put('taskovi/{task}/vrijeme/{timeEntry}', [TaskController::class, 'updateTime'])->name('tasks.time.update');
    Route::delete('taskovi/{task}/vrijeme/{timeEntry}', [TaskController::class, 'destroyTime'])->name('tasks.time.destroy');

    Route::get('vrijeme', [TimeEntryController::class, 'index'])->name('time.index');
    Route::post('vrijeme', [TimeEntryController::class, 'store'])->name('time.store');
    Route::put('vrijeme/{timeEntry}', [TimeEntryController::class, 'update'])->name('time.update');
    Route::delete('vrijeme/{timeEntry}', [TimeEntryController::class, 'destroy'])->name('time.destroy');

    Route::get('izvjestaji', [ReportController::class, 'index'])->name('reports.index');
    Route::post('izvjestaji', [ReportController::class, 'generate'])->name('reports.generate');
    Route::get('izvjestaji/export/pdf', [ReportExportController::class, 'pdf'])->name('reports.export.pdf');
    Route::get('izvjestaji/export/excel', [ReportExportController::class, 'excel'])->name('reports.export.excel');
    Route::get('izvjestaji/print', [ReportExportController::class, 'print'])->name('reports.print');

    Route::get('podesavanja', [SettingsController::class, 'edit'])->name('settings.edit');
    Route::put('podesavanja', [SettingsController::class, 'update'])->name('settings.update');

    Route::get('fajlovi/{attachment}/download', [AttachmentController::class, 'download'])->name('attachments.download');
    Route::get('api/attachments', [AttachmentUploadController::class, 'index'])->name('attachments.index');
    Route::post('api/attachments', [AttachmentUploadController::class, 'store'])->name('attachments.store');
    Route::delete('api/attachments/{attachment}', [AttachmentUploadController::class, 'destroy'])->name('attachments.destroy');

    Route::view('profile', 'profile')->name('profile');

    Route::post('logout', [AuthenticatedSessionController::class, 'destroy'])->name('logout');
});

require __DIR__.'/auth.php';
