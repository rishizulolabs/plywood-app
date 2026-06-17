<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class PublicCatalogController extends Controller
{
    public function index()
    {
        return view('customer.catalog.index');
    }
}
