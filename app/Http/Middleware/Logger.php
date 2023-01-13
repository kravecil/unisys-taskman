<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

use App\Models\Log;
use App\Models\User;

class Logger
{
    private $requestTime;

    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure(\Illuminate\Http\Request): (\Illuminate\Http\Response|\Illuminate\Http\RedirectResponse)  $next
     * @return \Illuminate\Http\Response|\Illuminate\Http\RedirectResponse
     */
    // public function handle(Request $request, Closure $next)
    // {
    //     $requestTime = microtime(true);

    //     $log = new Log();

        // $log->username = $request->user()->id;
        // $log->ip = $request->ip();
        // $log->url = $request->fullUrl();
        // $log->method = $request->method();
        // $log->content = $request->getContent();
        // $log->duration = number_format(microtime(true) - $requestTime, 3);

    //     $log->save();


    //     /** необходимо написать ф-ию terminate,
    //      * в ней записывать время запроса и код ответа
    //      */

    //     return $next($request);
    // }

    public function handle(Request $request, Closure $next)
    {
        $this->requestTime = microtime(true);

        return $next($request);
    }

    public function terminate($request, $response) {
        $log = new Log();

        $log->username = $request->user()->username;
        $log->ip = $request->ip();
        $log->url = $request->fullUrl();
        $log->method = $request->method();
        // $log->content = $request->getContent();
        $log->content = collect($request->all())->toJson();
        $log->duration = $this->requestTime;//number_format(microtime(true) - $requestTime, 3);
        $log->response_code = $response->status();
        $log->response_message = $response->statusText();

        $log->save();
    }
}
