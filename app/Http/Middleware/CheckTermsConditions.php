<?php

namespace App\Http\Middleware;

use Closure;

class CheckTermsConditions
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
        if (!$request->user()->acepta_terminos) {
            return redirect()->route('backend.terminos');
        }

        return $next($request);
    }
}
