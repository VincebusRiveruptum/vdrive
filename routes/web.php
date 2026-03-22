<?php

use App\Http\Controllers\ProfileController;
use Illuminate\Foundation\Application;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;

use App\Http\Controllers\DiskController;

Route::get('/', function () {
    return Inertia::render('home/Welcome', [
        'canLogin' => Route::has('login'),
        'canRegister' => Route::has('register'),
        'laravelVersion' => Application::VERSION,
        'phpVersion' => PHP_VERSION,
    ]);
});

Route::get('/dashboard', [DiskController::class, 'index'])
    ->middleware(['auth', 'verified'])->name('dashboard');

Route::middleware('auth')->group(function () {
    Route::post('/disk/upload', [DiskController::class, 'store'])->name('disk.upload');
    Route::get('/disk/download/{file}', [DiskController::class, 'download'])->name('disk.download');
    Route::post('/disk/folder', [DiskController::class, 'storeFolder'])->name('disk.folder.store');
    Route::delete('/disk/file/{file}', [DiskController::class, 'destroy'])->name('disk.file.destroy');
    Route::delete('/disk/folder/{folder}', [DiskController::class, 'destroyFolder'])->name('disk.folder.destroy');
    
    Route::put('/disk/file/{file}/rename', [DiskController::class, 'renameFile'])->name('disk.file.rename');
    Route::put('/disk/folder/{folder}/rename', [DiskController::class, 'renameFolder'])->name('disk.folder.rename');
    Route::post('/disk/copy', [DiskController::class, 'copyItem'])->name('disk.copy');
    
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

require __DIR__.'/auth.php';
