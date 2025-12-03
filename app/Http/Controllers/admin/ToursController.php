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
        $title = $serviceController->generateTourTitle($validated['highlights'], $validated['startDate']);

        $tour = Tour::create([
            'userId' => $user->id,
            'title' => $title,
            'startDate' => $validated['startDate'],
            'endDate' => $validated['endDate'],
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
}
