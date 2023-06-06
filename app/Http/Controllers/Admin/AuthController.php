<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;

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
            return redirect('/dashboard')
                ->with('message', "Logged In Successfully!")
                ->with('status', 'danger');
        } else {
            return redirect('login')
                ->with('error', "*Either Email/Password is incorrect, Try Again!")
                ->with('message', "*Either Email/Password is incorrect, Try Again!")
                ->with('status', 'danger')
                ->withInput($request->only('email', 'remember'));
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

    public function forgetPassword()
    {
        Auth::logout();
        return view('backend.forgetpassword');
    }

    public function sendResetLink(Request $request)
    {
        $request->validate([
            'email' => 'required|email|exists:users,email'
        ]);

        $token = Str::random(64);

        $check_token = DB::table('password_resets')->where([
            'email' => $request->email,
        ])->first();

        if ($check_token) {
            //update to new token
            DB::table('password_resets')->where([
                'email' => $request->email,
            ])->update([
                'token' => $token
            ]);
        } else {
            
            DB::table('password_resets')->insert([
                'email' => $request->email,
                'token' => $token,
                'created_at' => Carbon::now(),
            ]);
        }

        // dd($token,$check_token);

        $action_link = route('password.reset.form', ['token' => $token, 'email' => $request->email]);
        $body = "We are received a request to reset the password for <b>Your app Name </b> account associated with " . $request->email . ". You can reset your password by clicking the link below";

        Mail::send('mail.email-forgot', ['action_link' => $action_link, 'body' => $body], function ($message) use ($request) {
            $message->from(env('MAIL_USERNAME'), 'Admin Dashboard');
            $message->to($request->email, 'Your name')
                ->subject('Reset Password');
        });

        // from email id must be same as mail_host given in .env file

        return back()->with('success', 'We have e-mailed your password reset link!');
    }

    public function showResetForm(Request $request, $token = null)
    {
        return view('backend.reset_password')->with(['token' => $token, 'email' => $request->email]);
    }

    public function resetPassword(Request $request)
    {
        $request->validate([
            'email' => 'required|email|exists:users,email',
            'password' => 'required|min:5|confirmed',
            'password_confirmation' => 'required',
        ]);

        $check_token = DB::table('password_resets')->where([
            'email' => $request->email,
            'token' => $request->token,
        ])->first();

        if (!$check_token) {
            return back()->withInput()->with('fail', 'Invalid token');
        } else {

            User::where('email', $request->email)->update([
                'password' => Hash::make($request->password)
            ]);

            DB::table('password_resets')->where([
                'email' => $request->email
            ])->delete();

            return redirect()->route('login')->with('info', 'Your password has been changed! You can login with new password')->with('verifiedEmail', $request->email);
        }
    }
}
