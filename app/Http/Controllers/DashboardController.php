<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class DashboardController extends Controller
{
    public function index()
    {
        $user = Auth::user();

        // Redirect to role-specific dashboard
        if ($user->isAdmin()) {
            return redirect('/admin');
        } elseif ($user->isInstructor()) {
            return redirect()->route('instructor.dashboard');
        } elseif ($user->isStudent()) {
            return redirect()->route('student.dashboard');
        }

        // Fallback to generic dashboard
        return view('dashboard');
    }
}