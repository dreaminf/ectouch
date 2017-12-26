<?php

namespace App\Api\Middleware;

use Closure;

class TokenAuthenticate
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request $request
     * @param  \Closure $next
     * @return mixed
     */
    public function handle($request, Closure $next)
    {
        $token = Token::authorization();

        if ($token === false) {
            return show_error(10001, trans('message.token.invalid'));
        }

        if ($token === 'token-expired') {
            return show_error(10002, trans('message.token.expired'));
        }

        return $next($request);
    }

}