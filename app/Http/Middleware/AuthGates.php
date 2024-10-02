<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Symfony\Component\HttpFoundation\Response;

class AuthGates
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request?->user()?->load('permissions');

        if($user){
            
            $permissions = [];

            foreach($user->permissions as $singlePermission){
                $permissions[] = $singlePermission->name;
            }
            
            collect($permissions)->unique()->each(function ($permission) {
                Gate::define($permission, function($user) {
                    return true;
                });
            });
        }
        
        return $next($request);
    }
}
