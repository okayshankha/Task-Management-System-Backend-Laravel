<?php

namespace App\Http\Controllers;

use App\Employee;
use App\EmployeeProjectMap;
use App\LoginAccessModel;
use Illuminate\Http\Request;

class EmployeeController extends Controller
{
    public function allManager(Request $request)
    {
        // Admin only
        $manager_employees = [];

        $manager_employees_list = Employee::groupBy('manager_employees_id')
            ->where('manager_employees_id', '!=', null)
            ->pluck('manager_employees_id')
            ->toArray();

        foreach (Employee::whereIn('employee_id', $manager_employees_list)->get() as $employees) {
            $manager_employees[] = $this->parseEmployeeDetails($employees);
        }

        $this->data = config('JsonResponse.success');
        $this->data['manager'] = $manager_employees;
        return response()->json($this->data);
    }

    public function allEmployees(Request $request)
    {
        // For All
        /**
         *  Modification needed
         */

        $allEmployee = [];

        $filter = $request->input('filterByField');
        $value = $request->input('filterValue');;

        $valid_filters = ['project_id'];

        if ($filter && in_array($filter, $valid_filters)) {
            $employees = [];
            if($filter == $valid_filters[0]){
                
                $projectEmployees = EmployeeProjectMap::where('project_id', $value)->where($filter, $value)
                                                        ->where('status', '!=', config('GlobalValues.employeesProjectMapInvalid'))
                                                        ->pluck('login_access_id')
                                                        ->toArray();
                $employees = Employee::whereIn('login_access_id', $projectEmployees)->get();
                //dd($employees);
            }else{
                $employees = Employee::where($filter, $value)->where('status', '!=', config('GlobalValues.employeeDeleted'))->get();
            }

            foreach ($employees as $employee) {
                if ($employee->login_access_id != $request->token->login_access_id) {
                    $allEmployee[] = $this->parseEmployeeDetails($employee);
                }
            }
        } else {
            foreach (Employee::where('status', '!=', config('GlobalValues.employeeDeleted'))->get() as $employee) {
                if ($employee->login_access_id != $request->token->login_access_id) {
                    $allEmployee[] = $this->parseEmployeeDetails($employee);
                }
            }
        }

        $this->data = config('JsonResponse.success');
        $this->data['employees'] = $allEmployee;
        return response()->json($this->data);
    }

    public function assignManager(Request $request, $manager_employeeID, $subordinate_employeeID)
    {
        // Admin only
        $manager_employee = Employee::find($manager_employeeID);
        $subordinate_employee = Employee::find($subordinate_employeeID);

        if ($manager_employee && $subordinate_employee) {
            if ($subordinate_employee->manager_employees_id == $manager_employee->employee_id) {
                return response()->json(config('JsonResponse.error_same_maneger_is_already_assigned'));
            }
            $subordinate_employee->manager_employees_id = $manager_employee->employee_id;
            $subordinate_employee->modified_by_access_id = $request->token->login_access_id;
            $subordinate_employee->save();
            return response()->json(config('JsonResponse.success'));
        } else {
            return response()->json(config('JsonResponse.error_404_employee'));
        }
    }

    public function verifyEmployeRegistrations(Request $request, $employeeID)
    {
        // Admin only
        $employee = Employee::find($employeeID);
        if ($employee) {
            if ($employee->status == config('GlobalValues.employeeValid')) {
                return response()->json(config('JsonResponse.error_employee_is_already_verified'));
            }
            $employee->status = config('GlobalValues.employeeValid');
            $employee->modified_by_access_id = $request->token->login_access_id;
            $employee->save();

            $employee = LoginAccessModel::where('login_access_id',  $employee->login_access_id)->get()->first();
            $employee->status = config('GlobalValues.employeeValid');
            $employee->save();

            return response()->json(config('JsonResponse.success'));
        } else {
            return response()->json(config('JsonResponse.error_404_employee'));
        }
    }

    public function deleteEmployeRegistrations(Request $request, $employeeID)
    {
        // Admin only & Manager Only
        $employee = Employee::find($employeeID);
        if ($employee) {
            if ($employee->status == config('GlobalValues.employeeDeleted')) {
                return response()->json(config('JsonResponse.error_employee_is_already_deleted'));
            }
            $employee->status = config('GlobalValues.employeeDeleted');
            $employee->modified_by_access_id = $request->token->login_access_id;
            $employee->save();

            $employee = LoginAccessModel::where('login_access_id', $employee->login_access_id)->get()->first();
            $employee->status = config('GlobalValues.employeeDeleted');
            $employee->save();

            return response()->json(config('JsonResponse.success'));
        } else {
            return response()->json(config('JsonResponse.error_404_employee'));
        }
    }
}
