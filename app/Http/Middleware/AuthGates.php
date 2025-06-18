<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
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
        //$user = $request?->user()?->load('roles.permissions');

        $user = Auth::guard('admin')->user() ?? $request?->user();

        if($user){
            $user->load('roles.permissions');
            $permissions = [];

            foreach($user->roles as $role) {
                foreach($role->permissions as $singlePermission){
                    $permissions[] = $singlePermission->name;
                }
            }

            collect($permissions)->unique()->each(function ($permission): void {
                Gate::define($permission, function($user): true {
                    return true;
                });
            });
        }

        return $next($request);
    }
}
