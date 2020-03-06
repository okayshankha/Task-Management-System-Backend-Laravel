<?php

namespace App\Http\Middleware;


use App\TokenModel; 
use Closure;


class Authenticate
{
    private $data = [];
    /**
     * Handle an incoming request.
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle($request, Closure $next)
    {
        
        $token = $this->getTokenFromRequest($request);
        $status = 200;
        
        if ($token === false) {
            
            $this->data = config('JsonResponse.error_404_token');
            $status = 401;
        } else {
            
            $token = TokenModel::where('token', $token)->get()->first();
            if ($token && $token->status == config('GlobalValues.tokenValid')) {
                
                $request->merge(['token' => $token]);
            } else {
                $this->data = config('JsonResponse.error_403_token');
                $status = 401;
            }
        }

       
        $route = $request->path();
        if($route == 'api/register' || $route == 'api/logout' || $route == 'api/login'){
            return $next($request);
        }else{
            if(!empty($this->data)){
                return response()->json($this->data)->setStatusCode($status);
            }else{
                return $next($request);
            }
        }
        
    }

    private function getTokenFromRequest($request)
    {
        $header = $request->header('Authorization');
        if ($header) {
            return explode(" ", $header)[1];
        } else {
            return false;
        }
    }
}
