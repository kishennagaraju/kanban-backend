<?php

namespace App\Http\Middleware;

use Illuminate\Foundation\Http\Middleware\VerifyCsrfToken as Middleware;
use Illuminate\Support\Facades\Config;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;

class VerifyCustomToken extends Middleware
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @param  string|null  $guard
     * @return mixed
     */
    public function handle($request, $next, $guard = null)
    {
        $token = $request->query('access_token');
        if (!$token || $token !== Config::get('app.api_token')) {
            throw new BadRequestHttpException('Invalid Token');
        }

        return $next($request);
    }
}
