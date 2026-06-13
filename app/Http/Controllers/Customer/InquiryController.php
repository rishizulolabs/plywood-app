<?php

namespace App\Http\Controllers\Customer;

use App\Models\Inquiry;
use Illuminate\View\View;

class InquiryController extends CustomerController
{
    public function index(): View
    {
        $inquiries = Inquiry::query()
            ->where('customer_id', $this->customer()->id)
            ->with(['distributorProfile', 'items.product', 'quote'])
            ->latest()
            ->paginate(15);

        return view('customer.inquiries.index', compact('inquiries'));
    }
}
