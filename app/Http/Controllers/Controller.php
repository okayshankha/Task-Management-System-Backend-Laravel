<?php

namespace App\Http\Controllers;

use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Foundation\Bus\DispatchesJobs;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Illuminate\Routing\Controller as BaseController;

use App\Employee;
use App\EmployeeProjectMap;
use App\Project;
use App\Task;

class Controller extends BaseController
{
    use AuthorizesRequests, DispatchesJobs, ValidatesRequests;

    protected $data = [];

    protected function generateGUID()
    {
        $characters = 'ABCDEFGHIJKLMNOPQRSTUVWXYZ';

        // generate a pin based on 2 * 7 digits + a random character
        $pin = mt_rand(1000000, 9999999) . mt_rand(1000000, 9999999) . $characters[rand(0, strlen($characters) - 1)];

        return str_shuffle($pin);
    }

    protected function isEmployeeAssignedToProject($employee_login_access_id, $taskId)
    {
        $task = Task::find($taskId);
        $project_id = $task->project_id;

        $obj = EmployeeProjectMap::where('login_access_id', $employee_login_access_id)
            ->where('project_id', $project_id)
            ->get()->first();
        if ($obj) {
            return true;
        } else {
            return false;
        }
    }

    protected function parseTask($task)
    {
        if (class_basename($task) == "Task") {
            $project = Project::find($task->project_id);
            $assigned_to_employee = Employee::where('login_access_id', $task->assigned_to_login_access_id)->get()->first();
            $assigned_by_employee = Employee::where('login_access_id', $task->assigned_by_login_access_id)->get()->first();
            $created_by_employee = Employee::where('login_access_id', $task->created_by_login_access_id)->get()->first();
            $modified_by_employee = Employee::where('login_access_id', $task->modified_by_login_access_id)->get()->first();
            return [
                'task_details_id' => $task->task_details_id,
                'name' => $task->name,
                'parent_task_details_id' => $task->parent_task_details_id,
                'project_id' => $project,
                'description' => $task->description,
                'estimated_hours' => $task->estimated_hours,
                'actual_hours' => $task->actual_hours,
                //'assigned_to_login_access_id' => $task->assigned_to_login_access_id,
                'assigned_to_employee' => $this->parseEmployeeDetails($assigned_to_employee),

                //'assigned_by_login_access_id' => $task->assigned_by_login_access_id,
                'assigned_by_employee' => $this->parseEmployeeDetails($assigned_by_employee),

                'assignment_comment' => $task->assignment_comment,
                'assigned_at' => $task->assigned_at,
                //'created_by_login_access_id' => $task->created_by_login_access_id,
                'created_by_employee' => $this->parseEmployeeDetails($created_by_employee),

                //'modified_by_login_access_id' => $task->modified_by_login_access_id,
                'modified_by_employee' => $this->parseEmployeeDetails($modified_by_employee),
                'status' => $task->status,
                'associated_project_status' => $project->status
            ];
        } else {
            return null;
        }
    }

    protected function parseEmployeeDetails($employee)
    {
        if (class_basename($employee) == "Employee") {
            return [
                'employee_id' => $employee->employee_id,
                'login_access_id' => $employee->login_access_id,
                'fname' => $employee->fname,
                'mname' => $employee->mname,
                'lname' => $employee->lname,
                'email' => $employee->email,
                'mobile' => $employee->mobile,
                'address' => $employee->address,
                'status' => $employee->status,
            ];
        } else {
            return null;
        }
    }

    protected function parseProjectDetails($project)
    {
        if (class_basename($project) == "Project") {

            $manager_list = [];
            $manager = Employee::where('login_access_id', $project->manager_login_access_id)->get();
            foreach ($manager as $data) {
                $manager_list[] = $this->parseEmployeeDetails($data);
            }

            $assigned_employee_list = [];
            $assigned_employee = EmployeeProjectMap::where('project_id', $project->project_id)->pluck('login_access_id');
            foreach ($assigned_employee as $data) {
                $assigned_employee_list[] = $data;
            }

            return [
                'project_id' => $project->project_id,
                'name' => $project->name,
                'description' => $project->description,
                'estimated_hours' => $project->estimated_hours,
                'created_by_login_access_id' => $project->created_by_login_access_id,
                'modified_by_login_access_id' => $project->modified_by_login_access_id,
                'manager_list' => $manager_list,
                'assigned_employees' => $assigned_employee_list,
                'status' => $project->status,
            ];
        } else {
            return null;
        }
    }
}
