<?php

namespace Htop\Middleware;

use Closure;
use Htop\Broadcast\HtopRequestEvent;
use Htop\Events\NewRequestEvent;
use Htop\Storage\StorageManager;
use Illuminate\Http\Request;

class RequestLogger
{
    public function handle(Request $request, Closure $next)
    {
        $start = microtime(true);

        try {
            $response = $next($request);
        } catch (\Throwable $e) {
            $response = response('Exception: '.$e->getMessage(), $e->getCode());
        }

        $entry = [
            'method' => $request->method(),
            'path' => $request->path(),
            'status' => $response->status(),
            'duration' => round((microtime(true) - $start) * 1000, 2), // ms
            'timestamp' => now()->toDateTimeString(),
        ];

        event(new HtopRequestEvent($entry));
        app(StorageManager::class)->store($entry);

        // Broadcast event
        broadcast(new NewRequestEvent($entry));

        return $response;
    }
}
