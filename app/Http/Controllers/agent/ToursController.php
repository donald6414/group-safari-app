<?php

namespace App\Http\Controllers\agent;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Inertia\Inertia;
use App\Models\Tour;
use App\Models\Client;
use App\Models\Booking;
use App\Models\TourVehicleSeat;
use App\Helper\FileUploadHelper;

class ToursController extends Controller
{
    public function index()
    {
        $tours = Tour::withCount(['tourVehicles'])->where('status', 'active')->orderBy('created_at', 'desc')->get();
        $responseData = [
            'stats' => [
                [
                    'title' => 'Total Tours',
                    'value' => count($tours)
                ]
            ],
            'tours' => $tours
        ];
        return Inertia::render('agent/Tours', [
            'responseData' => $responseData
        ]);
    }

    public function show($id)
    {
        $tour = Tour::with([
            'tourVehicles' => [
                'tourVehicleSeats' => [
                    'bookings' => [
                        'client'
                    ]
                ]
            ],
        ])->withCount(['tourVehicles'])->find($id);

        $responseData = [
            'tour' => $tour
        ];

        // dd($responseData);

        return Inertia::render('agent/TourDetails', [
            'id' => $id,
            'responseData' => $responseData
        ]);
    }

    public function bookSeat(Request $request)
    {
        // dd($request->all());
        $request->validate([
            'seatId' => 'required|numeric|exists:tour_vehicle_seats,id',
            'vehicleId' => 'required|numeric|exists:tour_vehicles,id',
            'tourId' => 'required|numeric|exists:tours,id',
            'bookingType' => 'required|string|in:full,custom',
            'startDate' => 'required|string',
            'endDate' => 'required|string',
            'fullName' => 'required|string',
            'email' => 'nullable|email',
            'phone' => 'nullable|string',
            'gender' => 'nullable|string',
            'nationality' => 'required|string',
            'language' => 'required|string',
            'maritalStatus' => 'nullable|string',
        ]);

        $client = Client::create([
            'userId' => auth()->user()->id,
            'name' => $request->fullName,
            'email' => $request->email,
            'phone' => $request->phone,
            'gender' => $request->gender,
            'nationality' => $request->nationality,
            'language' => $request->language,
        ]);

        $booking = Booking::create([
            'clientId' => $client->id,
            'tourVehicleSeatId' => $request->seatId,
            'startDate' => $request->startDate,
            'endDate' => $request->endDate,
        ]);

        $vehicleSeat = TourVehicleSeat::findOrFail($request->seatId);
        $vehicleSeat->status = 'reserved';
        $vehicleSeat->save();

        return Inertia::location(route('agentTours'));
    }

    public function uploadPaymentReceipt(Request $request)
    {
        $request->validate([
            'bookingId' => 'required|numeric|exists:bookings,id',
            'paymentReceipt' => 'required|file|mimes:jpeg,jpg,png,gif,pdf|max:5120', // 5MB max
        ]);

        $booking = Booking::with('client')->findOrFail($request->bookingId);

        // Verify that the booking belongs to the current agent
        if ($booking->client->userId !== auth()->user()->id) {
            return back()->withErrors(['paymentReceipt' => 'You are not authorized to upload a receipt for this booking.']);
        }

        // Handle file upload
        if ($request->hasFile('paymentReceipt')) {
            $booking->paymentReceipt = FileUploadHelper::uploadAndSaveFile($request->file('paymentReceipt'), 'uploads', 'payment-receipts');
            $booking->save();
        }

        return back()->with('success', 'Payment receipt uploaded successfully.');
    }

    public function confirmBooking(Request $request)
    {
        $request->validate([
            'bookingId' => 'required|numeric|exists:bookings,id',
            'seatId' => 'required|numeric|exists:tour_vehicle_seats,id',
        ]);

        $booking = Booking::with('client')->findOrFail($request->bookingId);
        $seat = TourVehicleSeat::findOrFail($request->seatId);

        // Verify that the booking is for this seat
        if ($booking->tourVehicleSeatId !== $seat->id) {
            return back()->withErrors(['message' => 'Booking does not match the selected seat.']);
        }

        // Verify that booking has a payment receipt
        if (!$booking->paymentReceipt || trim($booking->paymentReceipt) === '') {
            return back()->withErrors(['message' => 'Payment receipt is required before confirming booking.']);
        }

        // Verify that booking status is active
        if ($booking->status !== 'active') {
            return back()->withErrors(['message' => 'Only active bookings can be confirmed.']);
        }

        // Verify that seat status is reserved
        if ($seat->status !== 'reserved') {
            return back()->withErrors(['message' => 'Seat must be in reserved status to confirm booking.']);
        }

        // Update booking status to confirmed_payment
        $booking->status = 'confirmed_payment';
        $booking->save();

        // Update seat status to booked
        $seat->status = 'booked';
        $seat->save();

        return back()->with('success', 'Booking confirmed successfully.');
    }
}
