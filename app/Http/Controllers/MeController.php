<?php

namespace App\Http\Controllers;

use App\Employee;
use Illuminate\Http\Request;

use function PHPSTORM_META\type;

class MeController extends Controller
{
    function index(Request $request)
    {
        $login_access_id = $request->token->login_access_id;

        $employee = Employee::where('login_access_id', $login_access_id)->get()->first();

        if ($employee) {
            $this->data = config('JsonResponse.success');

            $this->data['me'] = $this->parseEmployeeDetails($employee);

            $this->data['manager'] = null;
            if ($employee->manager_employees_id != null) {
                $employee_manager = Employee::where('login_access_id', $employee->manager_employees_id)->get()->first();
                $this->data['manager'] = $employee_manager ? $this->parseEmployeeDetails($employee_manager) : null;
            }

            $this->data['subordinates'] = [];
            $subordinates = Employee::where('manager_employees_id', $employee->login_access_id)->get();
            if ($subordinates) {
                foreach ($subordinates as $data) {
                    $this->data['subordinates'][] = $this->parseEmployeeDetails($data);
                }
            }
        } else {
            $this->data = config('JsonResponse.error');
        }
        return response()->json($this->data);
    }

    function getManager(Request $request)
    {
        $login_access_id = $request->token->login_access_id;

        $employee = Employee::where('login_access_id', $login_access_id)->get()->first();

        if ($employee) {
            $this->data = config('JsonResponse.success');
            $this->data['manager'] = null;
            if ($employee->manager_employees_id != null) {
                $employee_manager = Employee::where('login_access_id', $employee->manager_employees_id)->get()->first();
                $this->data['manager'] = $employee_manager ? $this->parseEmployeeDetails($employee_manager) : null;
            }
        } else {
            $this->data = config('JsonResponse.error');
        }
        return response()->json($this->data);
    }

    function getSubordinates(Request $request)
    {
        $login_access_id = $request->token->login_access_id;

        $employee = Employee::where('login_access_id', $login_access_id)->get()->first();

        if ($employee) {
            $this->data = config('JsonResponse.success');
            $this->data['subordinates'] = [];
            $subordinates = Employee::where('manager_employees_id', $employee->employee_id)->get();
            if ($subordinates) {
                foreach ($subordinates as $data) {
                    $this->data['subordinates'][] = $this->parseEmployeeDetails($data);
                }
            }
        } else {
            $this->data = config('JsonResponse.error');
        }
        return response()->json($this->data);
    }
}
