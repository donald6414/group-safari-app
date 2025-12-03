<?php

namespace App\Http\Controllers\admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Inertia\Inertia;
use App\Models\Tour;
use App\Models\User;
class DashboardController extends Controller
{
    public function index()
    {
        $tours = Tour::count();
        $agents = User::where('role', 'agent')->count();
        $admins = User::where('role', 'admin')->count();
        $totalUsers = $agents + $admins;
        $responseData = [
            [
                'title' => 'Tours',
                'value' => $tours
            ],
            [
                'title' => 'Agents',
                'value' => $agents
            ],
            [
                'title' => 'Admins',
                'value' => $admins
            ],
            [
                'title' => 'Total Users',
                'value' => $totalUsers
            ]
        ];
        return Inertia::render('admin/Dashboard', [
            'responseData' => $responseData
        ]);
    }

    public function createTour(Request $request)
    {
        $request->validate([
            'startDate' => 'required|string|max:255',
            'endDate' => 'required|string|max:255',
            'highlights' => 'required|array',
            'highlights.*' => 'required|numeric|min:1|exists:highlights,id',

        ]);

        // $tour = Tour::create([
        //     'startDate' => $request->startDate,
        //     'endDate' => $request->endDate,
        //     'highlights' => $request->highlights,
        // ]);
        return redirect()->route('adminTours')->with('success', 'Tour created successfully');
    }
}
