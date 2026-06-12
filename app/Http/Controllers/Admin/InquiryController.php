<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Inquiry;
use Illuminate\View\View;

class InquiryController extends Controller
{
    public function index(): View
    {
        $inquiries = Inquiry::query()
            ->with(['customer', 'distributorProfile'])
            ->latest()
            ->paginate(15);

        return view('admin.inquiries.index', compact('inquiries'));
    }
}
