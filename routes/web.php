<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AttachmentController;

Route::get('/', function () {
    return redirect('/admin/tickets');
});

// Rutas protegidas para adjuntos
Route::middleware(['auth'])->group(function () {
    Route::get('/attachments/{attachment}/download', [AttachmentController::class, 'download'])
        ->name('attachments.download');
    Route::get('/attachments/{attachment}/view', [AttachmentController::class, 'view'])
        ->name('attachments.view');
});

// Debug route
require __DIR__.'/debug.php';
