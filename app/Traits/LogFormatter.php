<?php

namespace App\Traits;

use Illuminate\Support\Facades\Log;

trait LogFormatter
{
    public function logInfo($channel, $message)
    {
        Log::channel($channel)->info($message);
    }

    public function logWarning($channel, $message, $error)
    {
        Log::channel($channel)->warning($message, [
                'ip_address' => request()->ip(),
                'user_agent' => request()->header('User-Agent'),
                'time' => now()->toDateTimeString(),
                'error' => $error->getMessage(),
            ]);
    }

    public function logError($error)
    {
        Log::channel('debug')->error($error->getMessage(), [
                'ip_address' => request()->ip(),
                'file' => $error->getFile(),
                'line' => $error->getLine(),
            ]);
    }

    public function logAuth($method, $auth)
    {
        if ($method == 'login') {
            Log::channel('auth')->info('User logged in', [
                'user' => $auth,
                'ip' => request()->ip(),
                'user_agent' => request()->userAgent(),
                'url' => request()->url(),
            ]);
        }

        if ($method == 'logout') {
            Log::channel('auth')->info('User logged out', [
                'user' => $auth,
                'ip' => request()->ip(),
                'user_agent' => request()->userAgent(),
                'url' => request()->url(),
            ]);
        }

        if ($method == 'failed') {
            Log::channel('auth')->warning('Login failed', [
                'user' => $auth,
                'ip' => request()->ip(),
                'user_agent' => request()->userAgent(),
                'url' => request()->url(),
            ]);
        }
    }
}
