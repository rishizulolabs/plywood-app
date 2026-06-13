<?php

namespace App\Http\Controllers\Distributor;

use App\Http\Controllers\Controller;
use App\Models\DistributorProfile;

abstract class DistributorController extends Controller
{
    protected function distributorProfile(): ?DistributorProfile
    {
        return auth()->user()->distributorProfile;
    }
}
