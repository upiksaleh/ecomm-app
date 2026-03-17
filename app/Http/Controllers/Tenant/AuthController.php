<?php

namespace App\Http\Controllers\Tenant;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class AuthController extends Controller
{
    private $guard = 'tenant';

    private $pageIdentifier = 'tenant';

    private function authGuard()
    {
        return Auth::guard($this->guard);
    }

    public function showLogin()
    {
        return inertia($this->pageIdentifier.'/Login');
    }

    public function login(Request $request)
    {

        $credentials = $request->validate([
            'email' => 'required',
            'password' => 'required',
        ]);

        if ($this->authGuard()->attempt($credentials)) {
            $request->session()->regenerate();

            return redirect('/dashboard');
        }

        return back()->withErrors([
            'email' => 'Invalid credentials',
        ]);

    }

    public function logout()
    {
        $this->authGuard()->logout();

        return redirect('/');
    }
}
