<?php

namespace App\Http\Controllers;

use App\EmployeeProjectMap;
use App\LoginAccessModel;
use App\Project;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Hash;

class ProjectController extends Controller
{
    function index(Request $request, $id = null)
    {
        $login_access_id = $request->token->login_access_id;
        $projects = EmployeeProjectMap::where('login_access_id', $login_access_id)->get();
        $project_ids = [];

        foreach ($projects as $data) {
            $project_ids[] = $data->project_id;
        }


        $projects_list = [];
        if ($id != null) {
            $projects = Project::where('project_id', $id)->get();
        } else {
            $projects = Project::whereIn('project_id', $project_ids)
                ->where('status', config('GlobalValues.projectInprogress'))
                ->get();
        }

        foreach ($projects as $project) {
            $projects_list[] = $this->parseProjectDetails($project);
        }

        $this->data = config('JsonResponse.success');
        $this->data['projects'] = $projects_list;
        return response()->json($this->data);
    }

    function markAsDone(Request $request, $project_id)
    {
        $project = Project::find($project_id);
        if ($project) {
            $project->status = config('GlobalValues.projectDone');
            $project->modified_by_login_access_id = $request->token->login_access_id;
            $project->save();
            return response()->json(config('JsonResponse.success'));
        } else {
            return response()->json(config('JsonResponse.error_404_project'));
        }
    }

    function markAsBacklog(Request $request, $project_id)
    {
        $project = Project::find($project_id);
        if ($project) {
            $project->status = config('GlobalValues.projectBacklog');
            $project->modified_by_login_access_id = $request->token->login_access_id;
            $project->save();
            return response()->json(config('JsonResponse.success'));
        } else {
            return response()->json(config('JsonResponse.error_404_project'));
        }
    }

    function markAsInprogress(Request $request, $project_id)
    {
        $project = Project::find($project_id);
        if ($project) {
            $project->status = config('GlobalValues.projectInprogress');
            $project->modified_by_login_access_id = $request->token->login_access_id;
            $project->save();
            return response()->json(config('JsonResponse.success'));
        } else {
            return response()->json(config('JsonResponse.error_404_project'));
        }
    }

    /**
     * Returns all projects that are in "backlog" state
     */
    function backlogs()
    {
        $projects_list = [];
        $projects = Project::where('status', config('GlobalValues.projectBacklog'))
            ->orderBy('updated_at')
            ->get();
        foreach ($projects as $project) {
            $projects_list[] = $this->parseProjectDetails($project);
        }

        $this->data = config('JsonResponse.success');
        $this->data['projects'] = $projects_list;
        return response()->json($this->data);
    }

    /**
     * Returns all projects that are in "inprogress" state
     */
    function inprogress()
    {
        $projects_list = [];
        $projects = Project::where('status', config('GlobalValues.projectInprogress'))
            ->orderBy('updated_at')
            ->get();
        foreach ($projects as $project) {
            $projects_list[] = $this->parseProjectDetails($project);
        }

        $this->data = config('JsonResponse.success');
        $this->data['projects'] = $projects_list;
        return response()->json($this->data);
    }

    /**
     * Returns all projects that are in "done" state
     */
    function done()
    {
        $projects_list = [];
        $projects = Project::where('status', config('GlobalValues.projectDone'))
            ->orderBy('updated_at', 'desc')
            ->get();
        foreach ($projects as $project) {
            $projects_list[] = $this->parseProjectDetails($project);
        }

        $this->data = config('JsonResponse.success');
        $this->data['projects'] = $projects_list;
        return response()->json($this->data);
    }

    function create(Request $request)
    {
        if ($request->input('manager_login_access_id') && !LoginAccessModel::where('login_access_id', $request->input('manager_login_access_id'))->get()->first()) {
            return response()->json(config('JsonResponse.error_404_employee'));
        }

        $project_id = null;

        while (Project::find($project_id = config('GlobalValues.projectID_prefix') . $this->generateGUID()));

        $project = new Project;
        $project->project_id = $project_id;
        $project->name = $request->input('name');
        $project->description = $request->input('description');
        $project->estimated_hours = $request->input('estimated_hours');
        $project->created_by_login_access_id = $request->token->login_access_id;
        $project->manager_login_access_id = $request->input('manager_login_access_id');
        $project->save();

        $assign_manager = new EmployeeProjectMap;
        $assign_manager->project_id = $project_id;
        $assign_manager->login_access_id = $request->input('manager_login_access_id');
        $assign_manager->created_by_login_access_id = $request->token->login_access_id;
        $assign_manager->project_id = $project_id;
        $assign_manager->save();

        $this->data = config('JsonResponse.success');
        return response()->json($this->data);
    }

    function assign(Request $request, $employeeLoginAccessID)
    {
        if (!LoginAccessModel::where('login_access_id', $employeeLoginAccessID)->get()->first()) {
            return response()->json(config('JsonResponse.error_404_employee'));
        } elseif (!$request->input('project_id') && !Project::find($request->input('project_id'))) {
            return response()->json(config('JsonResponse.error_404_project'));
        }

        if (EmployeeProjectMap::where('project_id', $request->input('project_id'))->where('login_access_id', $employeeLoginAccessID)->get()->first()) {
            return response()->json(config('JsonResponse.error_400_employee_already_assigned'));
        }

        $employeeProjectMap = new EmployeeProjectMap;
        $employeeProjectMap->project_id = $request->input('project_id');
        $employeeProjectMap->login_access_id = $employeeLoginAccessID;
        $employeeProjectMap->created_by_login_access_id = $request->token->login_access_id;
        $employeeProjectMap->save();
        return response()->json(config('JsonResponse.success'));
    }
}
