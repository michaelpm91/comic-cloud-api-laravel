<?php

namespace App\Http\Middleware;

use Closure;

use Monolog\Logger;
use Monolog\Handler\LogEntriesHandler;

use Request;

class AccessLog
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle($request, Closure $next)
    {
        $response = $next($request);


        $logentriesHandler = new LogEntriesHandler(env('LOG_ENTRIES_KEY'));
        $channel = "Access";
        $monolog = new Logger($channel);
        $monolog->pushHandler($logentriesHandler);
        $monolog->addInfo('This is a info logging message', [
            'version' => env('APP_API_VERSION'),
            'route' => $request->path(),
            'method' => $request->method()
        ]);
        $monolog->addWarning('This is a warning logging message', [
            'version' => env('APP_API_VERSION'),
            'route' => $request->path(),
            'method' => $request->method()
        ]);

        return $response;
    }
}
