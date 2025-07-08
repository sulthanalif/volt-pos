<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class UserAccessLog
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        // Jalankan request terlebih dahulu untuk memastikan semua proses selesai
        $response = $next($request);

        // Ambil informasi pengguna
        $user = Auth::user();

        // Ambil informasi rute
        $routeName = $request->route() ? $request->route()->getName() : 'N/A';
        $routePath = $request->path();
        $method = $request->method();
        $ipAddress = $request->ip();

        // Buat pesan log
        if ($user) {
            $logMessage = sprintf(
                'User: [%s, %s, %s], accessed route: [%s %s]',
                $user->name, // Asumsi ada kolom 'name' di model User
                $user->email,
                $ipAddress,
                $method,
                $routePath,
            );
        } else {
            $logMessage = sprintf(
                'Guest %s accessed route: [%s] %s (Name: %s)',
                $ipAddress,
                $method,
                $routePath,
                $ipAddress
            );
        }

        Log::channel('access')->info($logMessage);

        return $response;
    }
}
