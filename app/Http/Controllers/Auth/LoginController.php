<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;

class LoginController extends Controller
{
    public function __construct()
    {
        $this->middleware('guest')->except('logout');
    }

    public function login()
    {
        return view('auth.login');
    }

    public function doLogin(Request $request)
    {
        return $this->attemptLogin($request);
    }

    public function logout()
    {
        Auth::logout();

        return redirect()->route('dashboard');
    }

    protected function attemptLogin(Request $request)
    {
        if (method_exists($this, 'hasTooManyLoginAttempts') && $this->hasTooManyLoginAttempts($request)) {
            $this->fireLockoutEvent($request);

            return $this->sendLockoutResponse($request);
        }

        $request->validate([
            'username' => 'required',
            'password' => 'required',
        ]);

        $user = User::where('username', '=', $request->username)->first();
        if (! $user) {
            info('Attempted login by unauthorised user '.$request->username);
            throw ValidationException::withMessages([
                'authentication' => 'You have entered an invalid GUID or password',
            ]);
        }

        if (config('ldap.authentication')) {
            if (! \Ldap::authenticate($request->username, $request->password)) {
                throw ValidationException::withMessages([
                    'authentication' => 'You have entered an invalid GUID or password',
                ]);
            }
        }

        if (! config('ldap.authentication')) {
            if (! Hash::check($request->password, $user->password)) {
                throw ValidationException::withMessages([
                    'authentication' => 'You have entered an invalid GUID or password',
                ]);
            }
        }

        Auth::login($user);

        return redirect(route('dashboard'));
    }

    protected function looksLikeStudentAccount(string $username): bool
    {
        $user = User::where('username', '=', $username)->first();
        if ($user && $user->is_staff) {
            return false;
        }

        return preg_match('/^[0-9].+/', $username) === 1;
    }
}
