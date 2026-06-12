<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Support\AdminSettings;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class SettingsController extends Controller
{
    public function index(): View
    {
        return view('admin.settings.index', [
            'settings' => AdminSettings::all(),
        ]);
    }

    public function update(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'support_email' => ['required', 'email', 'max:255'],
            'support_phone' => ['nullable', 'string', 'max:20'],
            'require_distributor_approval' => ['sometimes', 'boolean'],
        ]);

        AdminSettings::put([
            'support_email' => $validated['support_email'],
            'support_phone' => $validated['support_phone'] ?? '',
            'require_distributor_approval' => $request->boolean('require_distributor_approval'),
        ]);

        return redirect()
            ->route('admin.settings.index')
            ->with('success', 'Settings saved successfully.');
    }
}
