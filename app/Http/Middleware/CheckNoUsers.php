<?php

namespace App\Http\Middleware;

use App\Models\User;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class CheckNoUsers
{
    /**
     * Handle an incoming request.
     */
    public function handle(Request $request, Closure $next): Response
    {
        // Jika sudah ada user, redirect ke login dengan pesan error
        if (User::count() > 0) {
            return redirect()->route('login')
                ->with('error', 'Registrasi sudah ditutup. Sistem hanya mendukung satu user.');
        }

        return $next($request);
    }
}
