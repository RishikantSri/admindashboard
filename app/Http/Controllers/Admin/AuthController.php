<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class AuthController extends Controller
{
    public function login()
    {
        if (Auth::check()) {
            return redirect()->route('dashboard');
        }
        return view('backend.login');
    }

    public function loginAuthenticate(Request $request)
    {
        // dd('testing..');
        $this->validate($request, [
            'email' => 'required|email',
            'password' => 'required'
        ]);

        if (Auth::attempt(['email' => $request->email, 'password' => $request->password], $request->get('remember'))) {
            return redirect('/dashboard');
        } else {
             return redirect('login')
            ->with('message',"*Either Email/Password is incorrect, Try Again!")
            ->with('status', 'danger')
            ->withInput($request->only('email','remember'));
        }
    }

    public function logout()
    {
        Auth::logout();
        return redirect()->route('login');
    }

    public function dashboard()
    {
        return view('backend.dashboard');
    }
}
