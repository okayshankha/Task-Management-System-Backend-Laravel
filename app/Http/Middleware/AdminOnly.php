<?php

namespace App\Http\Middleware;

use Illuminate\Support\Facades\Config;
use App\LoginAccessModel;
use Closure;

class AdminOnly
{
    private $data = [];
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle($request, Closure $next)
    {
        $this->data = config('JsonResponse.error_access_denied');
        $token = $request->token;
        if($token){
            $employee = LoginAccessModel::where('login_access_id', $token->login_access_id)->get()->first();
            
            if($employee){
                if($employee->role_id != config('GlobalValues.admin_role_id')){
                    return response()->json($this->data);
                }
            }
        }
        return $next($request);
    }
}
