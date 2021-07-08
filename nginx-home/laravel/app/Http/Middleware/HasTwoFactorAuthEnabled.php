<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Support\Facades\Auth;
use Illuminate\Auth\Middleware\Authenticate as Middleware;

class HasTwoFactorAuthEnabled extends Middleware
{
    public function handle($request, Closure $next, ...$guards)
    {
        if (null !== Auth::user() && is_null(Auth::user()->two_factor_secret)) {
            session(['modal_message' => 'You Need to Enable 2-Factor Auth']);
            return redirect(route('profile.show'));
        }

        return $next($request);
    }
}
