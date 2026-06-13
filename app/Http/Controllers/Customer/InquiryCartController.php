<?php

namespace App\Http\Controllers\Customer;

use Illuminate\View\View;

class InquiryCartController extends CustomerController
{
    public function index(): View
    {
        $cartItems = session('inquiry_cart', []);

        return view('customer.inquiry-cart.index', [
            'cartItems' => $cartItems,
        ]);
    }
}
