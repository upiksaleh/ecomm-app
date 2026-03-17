<?php

namespace App\Http\Controllers\Tenant;

use App\Http\Controllers\Controller;
use App\Models\Tenant\Customer;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class CustomerAuthController extends Controller
{
    private string $guard = 'customer';

    private function authGuard()
    {
        return Auth::guard($this->guard);
    }

    public function showLogin()
    {
        return inertia('tenant/customer/Login');
    }

    public function login(Request $request): RedirectResponse
    {
        $credentials = $request->validate([
            'email' => 'required|email',
            'password' => 'required',
        ]);

        if ($this->authGuard()->attempt($credentials, $request->boolean('remember'))) {
            $request->session()->regenerate();

            return redirect()->intended('/shop');
        }

        return back()->withErrors(['email' => 'Invalid credentials.'])->onlyInput('email');
    }

    public function showRegister()
    {
        return inertia('tenant/customer/Register');
    }

    public function register(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|max:255|unique:customers,email',
            'password' => 'required|string|min:8|confirmed',
        ]);

        $customer = Customer::create($data);

        $this->authGuard()->login($customer);
        $request->session()->regenerate();

        return redirect('/shop');
    }

    public function logout(Request $request): RedirectResponse
    {
        $this->authGuard()->logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect('/shop');
    }
}
