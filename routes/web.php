<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\SantriController;
use App\Http\Controllers\PresensiController;
use App\Http\Controllers\SantriDashboardController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\IzinController;

// Auth routes (Login is now the root page)
Route::get('/', [AuthController::class, 'showLoginForm'])->name('login');
Route::post('/', [AuthController::class, 'login']);
Route::post('/logout', [AuthController::class, 'logout'])->name('logout');

// Protected routes
Route::middleware(['auth'])->group(function () {
    // Izin/Permits
    Route::get('/izin', [IzinController::class, 'index'])->name('izin.index');
    Route::get('/izin/create', [IzinController::class, 'create'])->name('izin.create');
    Route::post('/izin', [IzinController::class, 'store'])->name('izin.store');
    Route::get('/izin/manage', [IzinController::class, 'manage'])->name('izin.manage');
    Route::post('/izin/{izin}/status', [IzinController::class, 'updateStatus'])->name('izin.update-status');

    // Profile Management
    Route::get('/profile', [ProfileController::class, 'index'])->name('profile.index');
    Route::put('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::put('/profile/password', [ProfileController::class, 'updatePassword'])->name('profile.password');

    // Attendance scanning
    Route::get('/scan', [PresensiController::class, 'index'])->name('presensi.scan');
    Route::post('/presensi', [PresensiController::class, 'store'])->name('presensi.store');
    Route::put('/presensi/update-status', [PresensiController::class, 'updateStatus'])->name('presensi.update-status');
    Route::delete('/presensi/{presensi}', [PresensiController::class, 'destroy'])->name('presensi.destroy');
    
    // API-like route to get all santris for face matching
    Route::get('/api/santris', [SantriController::class, 'index'])->name('api.santris');

    // Admin routes
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');
    Route::get('/kehadiran-sholat', [DashboardController::class, 'kehadiran'])->name('dashboard.kehadiran');
    Route::get('/kehadiran-sholat/export', [DashboardController::class, 'exportKehadiran'])->name('dashboard.kehadiran.export');
    Route::get('/santri', [SantriController::class, 'adminList'])->name('santri.index');
    Route::get('/santri/register', [SantriController::class, 'create'])->name('santri.create');
    Route::post('/santri/register', [SantriController::class, 'store'])->name('santri.store');
    Route::get('/santri/{santri}/edit', [SantriController::class, 'edit'])->name('santri.edit');
    Route::put('/santri/{santri}', [SantriController::class, 'update'])->name('santri.update');
    Route::delete('/santri/{santri}', [SantriController::class, 'destroy'])->name('santri.destroy');
    
    // Santri routes
    Route::get('/santri/dashboard', [SantriDashboardController::class, 'index'])->name('santri.dashboard');
    Route::get('/santri/dashboard/export', [SantriDashboardController::class, 'export'])->name('santri.dashboard.export');

    // Super Admin routes
    Route::middleware(['auth'])->group(function () {
        Route::get('/super-admin/dashboard', [\App\Http\Controllers\SuperAdminDashboardController::class, 'index'])->name('superadmin.dashboard');
        Route::get('/super-admin/asatidz', [\App\Http\Controllers\AsatidzController::class, 'index'])->name('asatidz.index');
        Route::get('/super-admin/asatidz/create', [\App\Http\Controllers\AsatidzController::class, 'create'])->name('asatidz.create');
        Route::post('/super-admin/asatidz', [\App\Http\Controllers\AsatidzController::class, 'store'])->name('asatidz.store');
        Route::get('/super-admin/asatidz/{asatidz}/edit', [\App\Http\Controllers\AsatidzController::class, 'edit'])->name('asatidz.edit');
        Route::put('/super-admin/asatidz/{asatidz}', [\App\Http\Controllers\AsatidzController::class, 'update'])->name('asatidz.update');
        Route::delete('/super-admin/asatidz/{asatidz}', [\App\Http\Controllers\AsatidzController::class, 'destroy'])->name('asatidz.destroy');
    });
});
