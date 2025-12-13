<?php

namespace App\Http\Controllers\admin;

use App\Http\Controllers\Controller;
use App\Http\Controllers\ServiceController;
use Illuminate\Http\Request;
use Inertia\Inertia;
use App\Models\Tour;
use App\Models\Highlight;
use App\Models\TourHighlight;
use App\Models\TourVehicle;
use App\Models\TourVehicleSeat;
use App\Models\Booking;
use Carbon\Carbon;

class ToursController extends Controller
{
    public function index()
    {
        $tours = Tour::withCount(['tourVehicles'])->orderBy('created_at', 'desc')->get();
        // dd($tours);

        $highlights = Highlight::all();

        $responseData = [
            'stats' => [
                [
                    'title' => 'Total Tours',
                    'value' => count($tours)
                ]
            ],
            'tours' => $tours,
            'highlights' => $highlights
        ];
        return Inertia::render('admin/Tours', [
            'responseData' => $responseData
        ]);
    }

    public function create(Request $request)
    {
        // dd($request->all());
        $validated = $request->validate([
            'startDate' => 'required|string',
            'endDate' => 'required|string',
            'language' => 'required|string',
            'highlights' => 'required|array|min:1',
            'highlights.*' => 'required|integer|exists:highlights,id',
        ]);

        // Validate date order
        if (strtotime($validated['startDate']) > strtotime($validated['endDate'])) {
            return back()->withErrors([
                'endDate' => 'The end date must be after the start date.',
            ]);
        }

        $user = $request->user();

        // Generate tour title using ServiceController
        $serviceController = new ServiceController();
        $title = $serviceController->generateTourTitle($validated['highlights'], Carbon::parse($validated['startDate'])->addDays(1));

        $tour = Tour::create([
            'userId' => $user->id,
            'title' => $title,
            'startDate' => Carbon::parse($validated['startDate'])->addDays(1),
            'endDate' => Carbon::parse($validated['endDate'])->addDays(1),
            'language' => $validated['language'],
            'status' => 'active',
        ]);

        $tourVehicle = TourVehicle::create([
            'tourId' => $tour->id
        ]);

        for ($i=0; $i < 6; $i++) { 
            TourVehicleSeat::create([
                'tourVehicleId' => $tourVehicle->id,
                'seatNumber' => $i + 1
            ]);
        }

        foreach ($validated['highlights'] as $highlightId) {
            TourHighlight::create([
                'tourId' => $tour->id,
                'highlightId' => $highlightId
            ]);
        }

        return Inertia::location(route('adminTours'));
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

        $vehicles = $tour->tourVehicles->pluck('id');

        $tourVehicles = TourVehicle::where('tourId', $id)->count();
        $tourVehicleSeats = TourVehicleSeat::whereIn('tourVehicleId', $vehicles)->count();
        $reservedSeats = TourVehicleSeat::whereIn('tourVehicleId', $vehicles)->where('status', 'reserved')->count();
        $availableSeats = TourVehicleSeat::whereIn('tourVehicleId', $vehicles)->where('status', 'available')->count();

        $responseData = [
            'stats' => [
                [
                    'title' => 'Total Vehicles',
                    'value' => $tourVehicles
                ],
                [
                    'title' => 'Total Seats',
                    'value' => $tourVehicleSeats
                ],
                [
                    'title' => 'Reserved Seats',
                    'value' => $reservedSeats
                ],
                [
                    'title' => 'Available Seats',
                    'value' => $availableSeats
                ],
            ],
            'tour' => $tour,
        ];

        // dd($responseData);

        return Inertia::render('admin/TourDetails', [
            'id' => $id,
            'responseData' => $responseData
        ]);
    }

    public function setReservationDueDate($id, Request $request){
        $data = $request->validate([
            'dueDate' => 'required|string'
        ]);

        $booking = Booking::with(['tourVehicleSeat.tourVehicle.tour'])->findOrFail($id);
        
        // Get the tour through relationships: Booking -> TourVehicleSeat -> TourVehicle -> Tour
        $tour = $booking->tourVehicleSeat?->tourVehicle?->tour;
        
        if (!$tour) {
            return redirect()->back()->withErrors([
                'dueDate' => 'Unable to find the associated tour for this booking.'
            ]);
        }

        $dueDate = Carbon::parse($data['dueDate'])->addDays(1);
        $tourStartDate = Carbon::parse($tour->startDate);

        // Validate that the due date is not greater than the tour start date
        if ($dueDate->gt($tourStartDate)) {
            return redirect()->back()->withErrors([
                'dueDate' => 'The reservation due date cannot be greater than the tour start date (' . $tourStartDate->format('M d, Y') . ').'
            ]);
        }

        $booking->reservationDueDate = $dueDate;
        $booking->reservedAt = Carbon::now();
        $booking->save();

        return redirect()->back()->with('success', 'Reservation due date is set successfully!');
    }

    public function addVehicle(Request $request, $id){
        $tour = Tour::findOrFail($id);

        $tourVehicle = TourVehicle::create([
            'tourId' => $tour->id
        ]);

        for ($i=0; $i < 6; $i++) { 
            TourVehicleSeat::create([
                'tourVehicleId' => $tourVehicle->id,
                'seatNumber' => $i
            ]);
        }

        return redirect()->back()->with('success', 'Vehicle Added successfully!');
    }

    public function deleteVehicle($id){
        $vehicle = TourVehicle::with(['tourVehicleSeats'])->findOrFail($id);
        
        // Check if vehicle has any reserved or booked seats
        $hasReservedOrBookedSeats = $vehicle->tourVehicleSeats->contains(function($seat) {
            return $seat->status === 'reserved' || $seat->status === 'booked';
        });
        
        if ($hasReservedOrBookedSeats) {
            return redirect()->back()->withErrors([
                'vehicle' => 'Cannot delete vehicle. It has reserved or booked seats.'
            ]);
        }
        
        // Delete the vehicle (seats will be deleted automatically due to cascade)
        $vehicle->delete();
        
        return redirect()->back()->with('success', 'Vehicle deleted successfully!');
    }
}
