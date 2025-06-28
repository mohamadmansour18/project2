<?php

namespace App\Http\Middleware;

use App\Enums\UserRole;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class RoleMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next , string $role): Response
    {
        $user = Auth::user();

        if(!$user || $user->role->value !== $role)
        {
            return response()->json([
                'title' => 'غير مصرح لك بالدخول !',
                'body' => 'ليس لديك صلاحية للوصول إلى هذا المورد',
                'statusCode' => 403
            ] , 403);
        }
        return $next($request);
    }
}
