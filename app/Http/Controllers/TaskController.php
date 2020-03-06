<?php

namespace App\Http\Controllers;


use App\Employee;
use App\EmployeeProjectMap;
use App\Role;
use App\LoginAccessModel;
use App\Project;
use App\Task;
use Illuminate\Http\Request;

class TaskController extends Controller
{
    function index(Request $request, $taskId)
    {
        if (!Task::find($taskId)) {
            return response()->json(config('JsonResponse.error_404_task'));
        }

        $task = Task::find($taskId);
        if ($task) {
            $this->data = config('JsonResponse.success');
            $this->data['task'] = $this->parseTask($task);
            return response()->json($this->data);
        } else {
            return response()->json(config('JsonResponse.error_404_task'));
        }
    }


    function allTasks(Request $request, $filterByField = null, $filterFieldValue = null)
    {
        $employee = LoginAccessModel::find($request->token->login_access_id);
        $role = null;
        if ($employee) {
            $role = Role::find($employee->role_id)->get()->first()->name;
        }

        $tasks = [];

        if ($role != 'admin') {
            $task_list = Task::all()->orderBy('modified_at', 'desc');
            foreach ($task_list as $task) {
                $tasks[] = $this->parseTask($task);
            }
        } else {

            $validFilters = ['manager_employees_id', 'task_details_id', 'project_id', 'employee_id', 'status', 'created_at'];
            if (!in_array($filterByField, $validFilters)) {
                $filterByField = null;
            }


            if ($filterByField && $filterFieldValue) {
                /**
                 * If filter by Manager then all the subordinate tasks will also get populated.
                 */
                if ($filterByField == 'manager_employees_id') {
                    /**
                     * For filter "manager_employees_id"
                     */
                    $employees = Employee::where('employee_id', $filterFieldValue);
                    if ($employees) {
                        $subordinates = $employees = Employee::where($filterByField,  $filterFieldValue)
                            ->pluck('login_access_id')
                            ->toArray();

                        $subordinates[] = (string) $employee->login_access_id;
                        $task_list = Task::whereIn('assigned_to_login_access_id', $subordinates)
                            ->orderBy('updated_at', 'desc')
                            ->get();
                    } else {
                        return response()->json(config('JsonResponse.error_404_employee'));
                    }
                } elseif ($filterByField == 'employee_id') {
                    /**
                     * For filter "employee_id"
                     */
                    $employee = Employee::where('employee_id', $filterFieldValue);
                    if ($employees) {
                        $task_list = Task::whereIn('assigned_to_login_access_id', $employee->login_access_id)
                            ->orderBy('updated_at', 'desc')
                            ->get();
                    } else {
                        return response()->json(config('JsonResponse.error_404_employee'));
                    }
                } else {
                    /**
                     * For filters other than "manager_employees_id" and "employee_id"
                     */
                    if ($filterByField == 'created_at') {
                        $tempDate = [];

                        foreach (explode('-', $filterFieldValue) as $val) {
                            if ($val < 10) {
                                $tempDate[] = "0" . $val;
                            } else {
                                $tempDate[] = $val;
                            }
                        }
                        $tempDate = implode('-', $tempDate);

                        $filterFieldValue = $tempDate . "%";
                        $task_list = Task::where($filterByField, 'like', $filterFieldValue)
                            ->orderBy('updated_at', 'desc')
                            ->get();
                    } else {
                        $task_list = Task::where($filterByField, $filterFieldValue)
                            ->orderBy('updated_at', 'desc')
                            ->get();
                    }
                }
            } else {
                /**
                 * All task List
                 */
                $task_list = Task::orderBy('updated_at', 'desc')
                    ->get();
            }

            foreach ($task_list as $task) {
                $tasks[] = $this->parseTask($task);
            }
        }

        $this->data = config('JsonResponse.success');
        $this->data['tasks'] = $tasks;
        return response()->json($this->data);
    }

    function pick(Request $request, $taskId)
    {
        $task = Task::find($taskId);
        $project = Project::find($task->project_id);
        if (!$task) {
            return response()->json(config('JsonResponse.error_404_task'));
        } elseif ($project && $project->status != config('GlobalValues.projectInprogress')) {
            return response()->json(config('JsonResponse.error_401_associated_project_is_not_in_inprogress_state'));
        } elseif ($this->isEmployeeAssignedToProject($request->input('assigned_to_login_access_id'), $taskId)) {
            return response()->json(config('JsonResponse.error_403_employee_is_not_assigned_to_project'));
        } elseif ($project && $project->status != config('GlobalValues.projectInprogress')) {
            return response()->json(config('JsonResponse.error_401_associated_project_is_not_in_inprogress_state'));
        } elseif ($task->assigned_to_login_access_id != null) {
            return response()->json(config('JsonResponse.error_403_task_is_already_assigned'));
        } elseif ($task->status != config('GlobalValues.taskValid')) {
            return response()->json(config('JsonResponse.error_401_task_is_not_in_valid_state'));
        } else {
            $task->assigned_to_login_access_id = $request->token->login_access_id;
            $task->assigned_by_login_access_id = $request->token->login_access_id;
            $task->assigned_at = date(config('GlobalValues.datatime_format'));
            $task->assignment_comment = $request->input('assignment_comment');
            $task->save();
            return response()->json(config('JsonResponse.success'));
        }
    }

    /**
     * We can change only Name, Description, estimated_hours and actual_hours
     */
    function edit(Request $request, $taskId)
    {
        $task = Task::find($taskId);
        $project = Project::find($task->project_id);
        if (!$task) {
            return response()->json(config('JsonResponse.error_404_task'));
        } else {
            if ($task->task_details_id == $request->input('parent_task_details_id')) {
                return response()->json(config('JsonResponse.error_403_parent_task_id_should_be_different'));
            }

            $parentTask = Task::find($request->input('parent_task_details_id'));
            if ($request->input('parent_task_details_id') && !$parentTask) {
                return response()->json(config('JsonResponse.error_404_task'));
            } elseif ($project && $project->status != config('GlobalValues.projectInprogress')) {
                return response()->json(config('JsonResponse.error_401_associated_project_is_not_in_inprogress_state'));
            } elseif ($task->project_id != $parentTask->project_id) {
                return response()->json(config('JsonResponse.error_401_parent_task_should_be_associated_with_same_project'));
            }
        }

        if ($request->input('name'))
            $task->name = $request->input('name');
        if ($request->input('parent_task_details_id'))
            $task->parent_task_details_id = $request->input('parent_task_details_id');
        if ($request->input('description'))
            $task->description = $request->input('description');
        if ($request->input('estimated_hours'))
            $task->estimated_hours = $request->input('estimated_hours');
        if ($request->input('actual_hours'))
            $task->actual_hours = $request->input('actual_hours');
        if ($request->input('status')) {
            if ($request->input('status') == config('GlobalValues.taskValid') || $request->input('status') == config('GlobalValues.taskInvalid')) {
                $task->status = $request->input('status');
            }
        }

        $task->modified_by_login_access_id = $request->token->login_access_id;

        $task->save();
        $projectStatus = $project->status;
        $project->status = '****';
        $project->save();
        $project->status = $projectStatus;
        $project->save();

        return response()->json(config('JsonResponse.success'));
    }

    function assign(Request $request)
    {
        $taskId = $request->input('task_id');
        $status = 200;


        if (!$taskId) {
            $status = 400;
            return response()->json(config('JsonResponse.error_404_task'))->setStatusCode($status);
        }



        $task = Task::find($taskId);
        $project = Project::find($task->project_id);
        if (!$task) {
            
            return response()->json(config('JsonResponse.error_404_task'));
        } elseif ($request->input('assigned_to_login_access_id') && !LoginAccessModel::where('login_access_id', $request->input('assigned_to_login_access_id'))->get()->first()) {
            return response()->json(config('JsonResponse.error_404_employee'));
        } elseif (!$this->isEmployeeAssignedToProject($request->input('assigned_to_login_access_id'), $taskId)) {
            return response()->json(config('JsonResponse.error_403_employee_is_not_assigned_to_project'));
        } elseif ($project && $project->status != config('GlobalValues.projectInprogress')) {
            return response()->json(config('JsonResponse.error_401_associated_project_is_not_in_inprogress_state'));
        } elseif ($task->status != config('GlobalValues.taskValid')) {
            return response()->json(config('JsonResponse.error_401_task_is_not_in_valid_state'));
        }


        if ($task) {
            if ($request->input('assigned_to_login_access_id')) {
                if ($task->assigned_to_login_access_id != null) {
                    return response()->json(config('JsonResponse.error_403_task_is_already_assigned'));
                }
                $task->assigned_to_login_access_id = $request->input('assigned_to_login_access_id');
                $task->assigned_by_login_access_id = $request->token->login_access_id;
                $task->assigned_at = date(config('GlobalValues.datatime_format'));
                $task->assignment_comment = $request->input('assignment_comment');
                $task->save();
                return response()->json(config('JsonResponse.success'));
            } else {
                return response()->json(config('JsonResponse.error_404_parameter'));
            }
        } else {
            
            return response()->json(config('JsonResponse.error_404_task'));
        }
    }

    function create(Request $request)
    {

        if ($request->input('project_id') && !Project::where('project_id', $request->input('project_id'))->get()->first()) {
            return response()->json(config('JsonResponse.error_404_project'));
        } elseif ($request->input('parent_task_details_id') && !Task::find($request->input('parent_task_details_id'))) {
            return response()->json(config('JsonResponse.error_404_task'));
        } elseif ($request->input('assigned_to_login_access_id') && !LoginAccessModel::where('login_access_id', $request->input('assigned_to_login_access_id'))->get()->first()) {
            return response()->json(config('JsonResponse.error_404_employee'));
        }

        $task = new Task;
        $task_id = null;
        while (Task::find($task_id = config('GlobalValues.taskID_prefix') . $this->generateGUID()));
        $task->task_details_id = $task_id;
        $task->name = $request->input('name');
        $task->project_id = $request->input('project_id');

        if ($request->input('parent_task_details_id')) {
            $parentTask = Task::where('task_details_id', $request->input('parent_task_details_id'))->get()->first();
            if (!$parentTask) {
                return response()->json(config('JsonResponse.error_404_task'));
            }
            $task->project_id = $parentTask->project_id;
        }


        $task->parent_task_details_id = $request->input('parent_task_details_id');

        $task->description = $request->input('description');
        $task->estimated_hours = $request->input('estimated_hours');
        $task->actual_hours = 0;

        if ($request->input('assigned_to_login_access_id')) {
            $employeeProjectMap = EmployeeProjectMap::where('login_access_id', $request->input('assigned_to_login_access_id'))
                ->where('project_id', $task->project_id)
                ->get()
                ->first();
            if (!$employeeProjectMap) {
                return response()->json(config('JsonResponse.error_403_employee_is_not_associated_with_project'));
            }

            $task->assigned_to_login_access_id = $request->input('assigned_to_login_access_id');
            $task->assigned_by_login_access_id = $request->token->login_access_id;
            $task->assigned_at = date(config('GlobalValues.datatime_format'));
            $task->assignment_comment = $request->input('assignment_comment');
        }

        $task->created_by_login_access_id = $request->token->login_access_id;
        $task->status = config('GlobalValues.taskValid');
        $task->save();

        return response()->json(config('JsonResponse.success'));
    }
}
