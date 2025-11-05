<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\ValidationException;
use App\Models\User;
use Illuminate\Support\Facades\Log;

class LoginController extends Controller
{
    public function index()
    {
        return view('auth.login');
    }

    public function store(Request $request)
    {
        $request->validate([
            'identity' => 'required|string',
            'password' => 'required|string'
        ]);

        $credentials = $this->getCredentials($request);
        $identityField = filter_var($request->identity, FILTER_VALIDATE_EMAIL) ? 'email' : 'username';

        $user = User::where($identityField, $request->identity)->first();

        if (!$user) {
            if ($request->ajax()) {
                return response()->json([
                    'errors' => [
                        'identity' => [$identityField === 'email'
                            ? 'Email tidak terdaftar'
                            : 'Username tidak terdaftar']
                    ]
                ], 422);
            }
            throw ValidationException::withMessages([
                'identity' => $identityField === 'email'
                    ? 'Email tidak terdaftar'
                    : 'Username tidak terdaftar',
            ]);
        }

        if (!Auth::attempt($credentials, $request->filled('remember'))) {
            if ($request->ajax()) {
                return response()->json([
                    'errors' => [
                        'password' => ['Password salah']
                    ]
                ], 422);
            }
            throw ValidationException::withMessages([
                'password' => 'Password salah',
            ]);
        }

        $request->session()->regenerate();

        if ($request->ajax()) {
            return response()->json([
                'success' => true,
                'redirect' => route('dashboard.dashboard')
            ]);
        }

        return redirect()->intended(route('dashboard.dashboard'));
    }

    protected function getCredentials(Request $request)
    {
        $identity = $request->input('identity');
        $password = $request->input('password');

        // Cek apakah input berupa email
        if (filter_var($identity, FILTER_VALIDATE_EMAIL)) {
            return [
                'email' => $identity,
                'password' => $password
            ];
        }

        return [
            'username' => $identity,
            'password' => $password
        ];
    }
}
