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
    public function handle(Request $request, Closure $next, $role)
    {
        // Pastikan user sudah login
        if (!Auth::check()) {
            return redirect('/login'); // Redirect ke login jika belum login
        }

        // Cek apakah user memiliki role yang sesuai
        // Ambil role pengguna saat ini untuk kejelasan kode
        $userRole = Auth::user()->role;
        $requiredRole = $role; // $role adalah role yang dibutuhkan, misal: 'admin'

        // Bandingkan role
        if ($userRole !== $requiredRole) {
            // Gunakan string interpolation (kutip dua) untuk menyisipkan variabel ke dalam pesan.
            abort(403, "Akses ditolak. Role Anda adalah '{$userRole}', sedangkan akses ini memerlukan role '{$requiredRole}'.");
        }

        return $next($request); // Jika role sesuai, lanjutkan request
    }
}
