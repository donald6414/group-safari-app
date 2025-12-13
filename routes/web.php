<?php

use Illuminate\Support\Facades\Route;
use Inertia\Inertia;
use Laravel\Fortify\Features;

// Route::get('/', function () {
//     return Inertia::render('Welcome', [
//         'canRegister' => Features::enabled(Features::registration()),
//     ]);
// })->name('home');

Route::get('/preview/due-date-email', function () {
    // Get query parameter to determine which scenario to show (default: reminder)
    $isDueDate = request()->query('reached', false);
    
    // Sample data for email preview
    $sampleData = [
        'packageTitle' => 'Serengeti Safari Adventure - 7 Days',
        'seatNumber' => 5,
        'clientName' => 'John Doe',
        'agentName' => 'Jane Smith',
        'isDueDate' => (bool) $isDueDate,
        'daysLeft' => $isDueDate ? 0 : 3,
    ];
    
    return view('emails.reservation-due-date-notification', ['data' => $sampleData]);
})->name('preview.due-date-email');

Route::get('/', function () {

    if (!auth()->user()) {
        return redirect()->route('login');
    }else{
        if (auth()->user()->role === 'admin') {
            return redirect()->route('adminDashboard');
        } else {
            return redirect()->route('agentDashboard');
        }
    }
})->name('home');

Route::get('dashboard', function () {
    if (auth()->user()->role === 'admin') {
        return redirect()->route('adminDashboard');
    } else {
        return redirect()->route('agentDashboard');
    }
    return redirect()->route('home');
})->middleware(['auth', 'verified'])->name('dashboard');

Route::get('admin/dashboard', [App\Http\Controllers\admin\DashboardController::class, 'index'])->middleware(['auth', 'verified', 'admin'])->name('adminDashboard');
Route::get('admin/tours', [App\Http\Controllers\admin\ToursController::class, 'index'])->middleware(['auth', 'verified', 'admin'])->name('adminTours');
Route::post('admin/tour/create', [App\Http\Controllers\admin\ToursController::class, 'create'])->middleware(['auth', 'verified', 'admin'])->name('adminTourCreate');
Route::get('admin/tour/show/{id}', [App\Http\Controllers\admin\ToursController::class, 'show'])->middleware(['auth', 'verified', 'admin'])->name('adminTourShow');
Route::get('admin/agents/list', [App\Http\Controllers\admin\AgentController::class, 'index'])->middleware(['auth', 'verified', 'admin'])->name('adminAgentsList');
Route::post('admin/agent/create', [App\Http\Controllers\admin\AgentController::class, 'create'])->middleware(['auth', 'verified', 'admin'])->name('adminAgentCreate');
Route::post('admin/agent/activate/{id}', [App\Http\Controllers\admin\AgentController::class, 'activate'])->middleware(['auth', 'verified', 'admin'])->name('adminAgentActivate');
Route::post('admin/agent/suspend/{id}', [App\Http\Controllers\admin\AgentController::class, 'suspend'])->middleware(['auth', 'verified', 'admin'])->name('adminAgentSuspend');
Route::post('admin/agent/suspend/{id}', [App\Http\Controllers\admin\AgentController::class, 'suspend'])->middleware(['auth', 'verified', 'admin'])->name('adminAgentSuspend');
Route::post('admin/agent/activate/{id}', [App\Http\Controllers\admin\AgentController::class, 'activate'])->middleware(['auth', 'verified', 'admin'])->name('adminAgentActivate');
Route::post('agent/confirm-booking', [App\Http\Controllers\agent\ToursController::class, 'confirmBooking'])->middleware(['auth', 'verified', 'admin'])->name('agentConfirmBooking');
Route::post('admin/set-reservation/due-date/{bookingId}', [App\Http\Controllers\admin\ToursController::class, 'setReservationDueDate'])->middleware(['auth', 'verified', 'admin'])->name('adminSetReservationDueDate');
Route::post('admin/add/vehicle/{tourId}', [App\Http\Controllers\admin\ToursController::class, 'addVehicle'])->middleware(['auth', 'verified', 'admin'])->name('addVehicle');
Route::delete('admin/delete/vehicle/{vehicleId}', [App\Http\Controllers\admin\ToursController::class, 'deleteVehicle'])->middleware(['auth', 'verified', 'admin'])->name('deleteVehicle');

// Agent
Route::get('agent/dashboard', [App\Http\Controllers\agent\DashboardController::class, 'index'])->middleware(['auth', 'verified', 'agent'])->name('agentDashboard');
Route::get('agent/tours', [App\Http\Controllers\agent\ToursController::class, 'index'])->middleware(['auth', 'verified', 'agent'])->name('agentTours');
Route::get('agent/tour-details/{id}', [App\Http\Controllers\agent\ToursController::class, 'show'])->middleware(['auth', 'verified', 'agent'])->name('agentTourDetails');
Route::post('agent/book-seat', [App\Http\Controllers\agent\ToursController::class, 'bookSeat'])->middleware(['auth', 'verified', 'agent'])->name('agentBookSeat');
Route::post('agent/upload-payment-receipt', [App\Http\Controllers\agent\ToursController::class, 'uploadPaymentReceipt'])->middleware(['auth', 'verified', 'agent'])->name('agentUploadPaymentReceipt');

require __DIR__.'/settings.php';
