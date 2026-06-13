<?php

namespace App\Http\Controllers\Customer;

use App\Http\Controllers\Controller;
use App\Models\User;

abstract class CustomerController extends Controller
{
    protected function customer(): User
    {
        return auth()->user();
    }
}
