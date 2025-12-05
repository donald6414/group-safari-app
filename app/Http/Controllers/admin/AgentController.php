<?php

namespace App\Http\Controllers\admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules\Password;
use Inertia\Inertia;
use App\Models\User;
use Illuminate\Support\Facades\Mail;
use App\Mail\InvitationMail;
use Illuminate\Auth\Events\Registered;

class AgentController extends Controller
{
    public function index()
    {
        $agents = User::where('role', 'agent')->get();

        $responseData = [
            'stats' => [
                [
                    'title' => 'Total Agents',
                    'value' => count($agents)
                ]
            ],
            'agents' => $agents
        ];
        
        return Inertia::render('admin/Agents', [
            'responseData' => $responseData
        ]);
    }

    public function create(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users',
            'phone' => 'nullable|string|max:255',
        ]);

        // Generate a random password
        $password = \Illuminate\Support\Str::random(12);

        // Create the agent user
        $user = User::create([
            'name' => $validated['name'],
            'email' => $validated['email'],
            'phone' => $validated['phone'] ?? null,
            'password' => Hash::make($password),
            'role' => 'agent',
            'status' => 'active',
        ]);

        $data = [
            'name' => $user->name,
            'email' => $user->email,
            'password' => $password,
        ];

        Mail::to($user->email)->send(new InvitationMail($data));

        event(new Registered($user));

        return redirect()->back();
    }

    public function suspend($id)
    {
        $user = User::findOrFail($id);
        
        if ($user->role !== 'agent') {
            return back()->withErrors(['error' => 'User is not an agent']);
        }
        
        $user->status = 'inactive';
        $user->save();
        
        return redirect()->route('adminAgentsList')->with('success', 'Agent account suspended successfully');
    }

    public function activate($id)
    {
        $user = User::findOrFail($id);
        
        if ($user->role !== 'agent') {
            return back()->withErrors(['error' => 'User is not an agent']);
        }
        
        $user->status = 'active';
        $user->save();
        
        return redirect()->route('adminAgentsList')->with('success', 'Agent account activated successfully');
    }
}
