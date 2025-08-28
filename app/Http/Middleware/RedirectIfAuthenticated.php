<?php

namespace App\Http\Middleware;

use App\Providers\RouteServiceProvider;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class RedirectIfAuthenticated
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure(\Illuminate\Http\Request): (\Illuminate\Http\Response|\Illuminate\Http\RedirectResponse)  $next
     * @param  string|null  ...$guards
     * @return \Illuminate\Http\Response|\Illuminate\Http\RedirectResponse
     */
    public function handle(Request $request, Closure $next, ...$guards)
    {
        $guards = empty($guards) ? [null] : $guards;

        foreach ($guards as $guard) {
            if (Auth::guard($guard)->check()) {

                $role = Auth::user()->role;

                // Alihkan berdasarkan peran pengguna
                switch ($role) {
                    case 'admin':
                        return redirect()->route('dashboard.kasir');
                        break;
                    case 'bos':
                        return redirect()->route('dashboard.bos');
                        break;
                    case 'kasir':
                        return redirect()->route('dashboard.kasir');
                        break;
                    case 'supervisor':
                        return redirect()->route('dashboard.kasir');
                        break;
                    case 'pemandu':
                        return redirect()->route('dashboard.pemandu');
                        break;
                    default:
                        // Jika ada peran lain yang tidak terduga, arahkan ke halaman utama
                        return redirect(RouteServiceProvider::HOME);
                        break;
                }
                // --- AKHIR LOGIC BARU ---
            }
        }

        return $next($request);
    }
}
