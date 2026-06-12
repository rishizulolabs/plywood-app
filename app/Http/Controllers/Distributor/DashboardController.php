<?php

namespace App\Http\Controllers\Distributor;

use App\Http\Controllers\Controller;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function index(): View
    {
        $profile = auth()->user()->distributorProfile;

        return view('distributor.dashboard', compact('profile'));
    }
}
