<?php

namespace App\Http\Middleware;

use App\Employee;
use App\LoginAccessModel;
use Closure;

class ManagerOnly
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
        $this->data = config('JsonResponse.error_access_denied');
        $token = $request->token;
        if($token){
            $employee = LoginAccessModel::where('login_access_id', $token->login_access_id)->get()->first();
            
            if($employee){
                if($employee->role_id == config('GlobalValues.admin_role_id')){
                    return $next($request);
                }else{
                    $employee_id = Employee::where('login_access_id', $token->login_access_id)
                                    ->pluck('employee_id')
                                    ->toArray();
                    $manager_employee = Employee::where('manager_employees_id', $employee_id[0])->get()->first();   
                    dd($manager_employee);
                    if($manager_employee){
                        return $next($request);
                    }   
                }
            }
        }
        return response()->json($this->data);
    }
}
