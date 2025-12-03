<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Inertia\Inertia;

class ToursController extends Controller
{
    public function index()
    {
        return Inertia::render('admin/Tours');
    }

    public function agentIndex()
    {
        return Inertia::render('agent/Tours');
    }
}
