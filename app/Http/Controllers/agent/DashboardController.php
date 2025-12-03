<?php

namespace App\Http\Controllers\agent;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Inertia\Inertia;
use App\Models\Tour;

class DashboardController extends Controller
{
    public function index()
    {
        $tours = Tour::where('status', 'active')->count();

        $responseData = [
            'stats' => [
                [
                    'title' => 'Active Tours',
                    'value' => $tours
                ]
            ]
        ];

        return Inertia::render('agent/Dashboard', [
            'responseData' => $responseData
        ]);
    }
}
