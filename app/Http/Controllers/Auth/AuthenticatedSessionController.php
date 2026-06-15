<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\LoginRequest;
use App\Support\CustomerCart;
use App\Support\RedirectsToRoleDashboard;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class AuthenticatedSessionController extends Controller
{
    use RedirectsToRoleDashboard;

    public function create(): View
    {
        return view('auth.login');
    }

    public function store(LoginRequest $request): RedirectResponse
    {
        $request->authenticate();

        $user = $request->user();

        if ($user && $user->hasRole('customer')) {
            CustomerCart::mergeSessionItems($user, $request->session()->get('cart', []));
            $request->session()->forget('cart');
        }

        $request->session()->regenerate();

        return $this->redirectToRoleDashboard($request->user());
    }

    public function destroy(Request $request): RedirectResponse
    {
        Auth::guard('web')->logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect('/');
    }
}
