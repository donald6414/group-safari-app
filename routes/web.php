<?php

use Illuminate\Support\Facades\Route;
use Inertia\Inertia;
use Laravel\Fortify\Features;

// Route::get('/', function () {
//     return Inertia::render('Welcome', [
//         'canRegister' => Features::enabled(Features::registration()),
//     ]);
// })->name('home');
Route::get('/', function () {
    return redirect()->route('adminDashboard');
})->name('home');

// Route::get('dashboard', function () {
//     return Inertia::render('Dashboard');
// })->middleware(['auth', 'verified'])->name('dashboard');

Route::get('admin/dashboard', [App\Http\Controllers\admin\DashboardController::class, 'index'])->middleware(['auth', 'verified', 'admin'])->name('adminDashboard');
Route::get('admin/tours', [App\Http\Controllers\admin\ToursController::class, 'index'])->middleware(['auth', 'verified', 'admin'])->name('adminTours');
Route::post('admin/tour/create', [App\Http\Controllers\admin\ToursController::class, 'create'])->middleware(['auth', 'verified', 'admin'])->name('adminTourCreate');
Route::get('admin/tour/show/{id}', [App\Http\Controllers\admin\ToursController::class, 'show'])->middleware(['auth', 'verified', 'admin'])->name('adminTourShow');
Route::get('admin/agents/list', [App\Http\Controllers\admin\AgentController::class, 'index'])->middleware(['auth', 'verified', 'admin'])->name('adminAgentsList');
Route::post('admin/agent/create', [App\Http\Controllers\admin\AgentController::class, 'create'])->middleware(['auth', 'verified', 'admin'])->name('adminAgentCreate');

// Agent
Route::get('agent/dashboard', [App\Http\Controllers\agent\DashboardController::class, 'index'])->middleware(['auth', 'verified', 'agent'])->name('agentDashboard');
Route::get('agent/tours', [App\Http\Controllers\agent\ToursController::class, 'index'])->middleware(['auth', 'verified', 'agent'])->name('agentTours');
Route::get('agent/tour-details/{id}', [App\Http\Controllers\agent\ToursController::class, 'show'])->middleware(['auth', 'verified', 'agent'])->name('agentTourDetails');
Route::post('agent/book-seat', [App\Http\Controllers\agent\ToursController::class, 'bookSeat'])->middleware(['auth', 'verified', 'agent'])->name('agentBookSeat');

require __DIR__.'/settings.php';
