<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class EnsureStripeConfig
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle(Request $request, Closure $next)
    {
        config(['cashier.key' => Auth::user()->company->cashier_stripe_key]);
        config(['cashier.secret' => Auth::user()->company->cashier_stripe_secret]);

        return $next($request);
    }
}
