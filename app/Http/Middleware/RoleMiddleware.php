<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class RoleMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @param  string  $role
     * @return mixed
     */
    public function handle(Request $request, Closure $next, $roles)
    {
        // Pastikan user sudah login
        if (!Auth::check()) {
            return redirect('/login');
        }
    
        $userRole = Auth::user()->role;
    
        // Pisahkan role yang dikirim (bisa pakai 'kasir,admin' atau 'kasir|admin')
        $allowedRoles = preg_split('/[|,]/', $roles);
    
        // Cek apakah role user ada di dalam daftar allowedRoles
        if (!in_array($userRole, $allowedRoles)) {
            abort(403, "Akses ditolak. Role Anda adalah '{$userRole}', sedangkan akses ini memerlukan salah satu role: " . implode(', ', $allowedRoles) . ".");
        }
    
        return $next($request);
    }
}
