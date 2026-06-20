<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\WarrantyClaim;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class WarrantyClaimController extends Controller
{
    public function index(): View
    {
        $claims = WarrantyClaim::query()
            ->with(['user', 'order', 'media'])
            ->latest()
            ->paginate(15);

        $stats = [
            [
                'label' => 'Total claims',
                'value' => WarrantyClaim::count(),
                'desc' => 'All warranty requests',
                'color' => 'blue',
                'icon' => 'icon-file-text',
            ],
            [
                'label' => 'Pending review',
                'value' => WarrantyClaim::where('status', 'pending')->count(),
                'desc' => 'Awaiting admin action',
                'color' => 'amber',
                'icon' => 'icon-activity',
            ],
            [
                'label' => 'Resolved',
                'value' => WarrantyClaim::whereIn('status', ['approved', 'resolved'])->count(),
                'desc' => 'Approved or resolved',
                'color' => 'green',
                'icon' => 'icon-check-circle',
            ],
        ];

        return view('admin.warranty-claims.index', compact('claims', 'stats'));
    }

    public function updateStatus(Request $request, WarrantyClaim $warrantyClaim): RedirectResponse
    {
        $validated = $request->validate([
            'status' => ['required', 'in:pending,reviewing,approved,rejected,resolved'],
            'admin_notes' => ['nullable', 'string', 'max:2000'],
        ]);

        $warrantyClaim->update([
            'status' => $validated['status'],
            'admin_notes' => $validated['admin_notes'] ?? $warrantyClaim->admin_notes,
        ]);

        return back()->with('success', 'Warranty claim updated.');
    }
}
