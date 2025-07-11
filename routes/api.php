<?php

use App\Http\Controllers\Api\TicketController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "api" middleware group. Make something great!
|
*/

Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});

/*
|--------------------------------------------------------------------------
| Public API Routes - Tickets
|--------------------------------------------------------------------------
|
| These routes are publicly accessible without authentication
|
*/

// Get ticket details by ticket number (public endpoint)
Route::get('/tickets/{ticketNumber}', [TicketController::class, 'show'])
    ->name('api.tickets.show')
    ->where('ticketNumber', '[0-9]+');

// Alternative endpoint with explicit "public" prefix for clarity
Route::get('/public/tickets/{ticketNumber}', [TicketController::class, 'show'])
    ->name('api.public.tickets.show')
    ->where('ticketNumber', '[0-9]+');
