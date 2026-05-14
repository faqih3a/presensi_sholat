<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class CheckRole
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     * @param  string[]  ...$roles
     */
    public function handle(Request $request, Closure $next, ...$roles): Response
    {
        if (!$request->user() || !in_array($request->user()->role, $roles)) {
            // Jika user adalah santri, arahkan ke dashboard santri
            if ($request->user() && $request->user()->role === 'santri') {
                return redirect()->route('santri.dashboard')->with('error', 'Anda tidak memiliki akses ke halaman tersebut.');
            }
            
            // Jika user adalah asatidz, arahkan ke dashboard asatidz
            if ($request->user() && $request->user()->role === 'asatidz') {
                return redirect()->route('dashboard')->with('error', 'Anda tidak memiliki akses ke halaman tersebut.');
            }

            // Jika tidak ada user atau role lain, arahkan ke login
            if (!$request->user()) {
                return redirect()->route('login')->with('error', 'Silakan login terlebih dahulu.');
            }

            return redirect()->route('login')->with('error', 'Anda tidak memiliki akses ke halaman tersebut.');
        }

        return $next($request);
    }
}
