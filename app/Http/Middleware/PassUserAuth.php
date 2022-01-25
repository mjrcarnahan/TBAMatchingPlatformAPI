<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use JWTAuth;
use App\Models\User;

class PassUserAuth
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
        $user_tmp = JWTAuth::user();
        $user_auth = User::with(['profile'])->where('id',$user_tmp->id)->first();

        $request->merge(['user_auth' => $user_auth]);
        return $next($request);
    }
}
