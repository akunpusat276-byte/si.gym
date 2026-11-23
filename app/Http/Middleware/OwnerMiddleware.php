<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class OwnerMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  Closure(Request): (Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        abort_unless($request->user()?->hasRole('owner'), 403, 'Anda tidak memiliki hak akses untuk halaman ini.');
        $isOwnProfileUpdate = $request->isMethod('PUT')
            && $request->routeIs('owner.profile.update', 'owner.password.update');
        abort_unless(
            $request->isMethod('GET') || $request->isMethod('HEAD') || $isOwnProfileUpdate,
            403,
            'Pemilik hanya memiliki akses baca dan tidak dapat mengubah data.'
        );

        return $next($request);
    }
}
