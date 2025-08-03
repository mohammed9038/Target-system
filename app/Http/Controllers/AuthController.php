<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class AuthController extends Controller
{
    public function showLoginForm()
    {
        if (Auth::check()) {
            return redirect()->route('dashboard');
        }
        
        return view('auth.login');
    }

    public function login(Request $request)
    {
        $credentials = $request->validate([
            'username' => 'required|string',
            'password' => 'required|string',
        ]);

        if (Auth::attempt($credentials, $request->boolean('remember'))) {
            $request->session()->regenerate();
            return redirect()->intended(route('dashboard'));
        }

        return back()->withErrors([
            'username' => 'The provided credentials do not match our records.',
        ])->onlyInput('username');
    }

    public function logout(Request $request)
    {
        try {
            \Log::info('Logout attempt', [
                'user' => Auth::user() ? Auth::user()->username : 'None',
                'session_id' => $request->session()->getId(),
                'csrf_token' => $request->session()->token(),
            ]);
            
            Auth::logout();
            $request->session()->invalidate();
            $request->session()->regenerateToken();
            
            \Log::info('Logout successful');
            return redirect()->route('login')->with('status', 'Successfully logged out');
            
        } catch (\Exception $e) {
            \Log::error('Logout error: ' . $e->getMessage());
            return redirect()->route('login')->with('error', 'Logout failed');
        }
    }
} 