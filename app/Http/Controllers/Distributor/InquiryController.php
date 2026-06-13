<?php

namespace App\Http\Controllers\Distributor;

use App\Models\Inquiry;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\View\View;

class InquiryController extends DistributorController
{
    public function index(): View
    {
        $profile = $this->distributorProfile();

        $inquiries = $profile
            ? Inquiry::query()
                ->where('distributor_profile_id', $profile->id)
                ->with(['customer', 'items.product'])
                ->latest()
                ->paginate(15)
            : new LengthAwarePaginator([], 0, 15);

        return view('distributor.inquiries.index', compact('profile', 'inquiries'));
    }
}
