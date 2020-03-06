<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/

/**
 * --------------------------------------OAuthContorller---------------------------------------------------------
 */

Route::post('/register', 'OAuthContorller@register')->middleware('authenticate');
Route::post('/login', 'OAuthContorller@login');
Route::get('/logout', 'OAuthContorller@logout')->middleware('authenticate');


/**
 * --------------------------------------MeController------------------------------------------------------------
 */

// Returns My details including "My Manager" and "My Subordinates"
Route::get('/me', 'MeController@index')->middleware('authenticate');
// Returns only Manager details
Route::get('/me/manager', 'MeController@getManager')->middleware('authenticate');
// Returns only Subordinate details
Route::get('/me/subordinates', 'MeController@getSubordinates')->middleware('authenticate');


/**
 * --------------------------------------ProjectController-------------------------------------------------------
 */

// Returns All Project details I am assigned to. [All projects will be in-progress]
Route::get('/projects', 'ProjectController@index')->middleware('authenticate');
Route::get('/projects/{id}', 'ProjectController@index')->middleware('authenticate');
Route::post('/projects/assign/{employeeLoginAccessID}', 'ProjectController@assign')->middleware('authenticate');
// Returns All Projects details based on its status [Admin only Part]
Route::get('/projects/backlog', 'ProjectController@backlogs')->middleware(['authenticate', 'adminonly']);
Route::get('/projects/inprogress', 'ProjectController@inprogress')->middleware(['authenticate', 'adminonly']);
Route::get('/projects/done', 'ProjectController@done')->middleware(['authenticate', 'adminonly']);
// Adds new project to the system [Admin only Part]
Route::post('/projects/create', 'ProjectController@create')->middleware(['authenticate', 'adminonly']);

// Changes Project Status
Route::get('/projects/mark/done/{project_id}', 'ProjectController@markAsDone')->middleware(['authenticate', 'manageronly']);
Route::get('/projects/mark/backlog/{project_id}', 'ProjectController@markAsBacklog')->middleware(['authenticate', 'manageronly']);
Route::get('/projects/mark/inprogress/{project_id}', 'ProjectController@markAsInprogress')->middleware(['authenticate', 'manageronly']);

/**
 * --------------------------------------TaskController---------------------------------------------------------
 */

// Adds new task for a existing project
Route::post('/task/create', 'TaskController@create')->middleware('authenticate');
Route::post('/task/assign',  'TaskController@assign')->middleware('authenticate');
// Get all the details of task
Route::post('/task/{taskId}', 'TaskController@index')->middleware('authenticate');
// Edit all the details of task
Route::post('/task/edit/{taskId}', 'TaskController@edit')->middleware('authenticate');

Route::get('/task/pick/{taskId}',  'TaskController@pick')->middleware('authenticate');
Route::get('/task/all', 'TaskController@allTasks')->middleware('authenticate');
Route::get('/task/filter/{filterByField}/{filterFieldValue}', 'TaskController@allTasks')->middleware('authenticate');


/**
 * --------------------------------------EmployeeController----------------------------------------------------
 */

// Returns all Employees
Route::get('/employees', 'EmployeeController@allEmployees')->middleware(['authenticate']);
Route::post('/employees/filter', 'EmployeeController@allEmployees')->middleware(['authenticate']);
// Returns all Managers
Route::get('/employees/managers', 'EmployeeController@allManager')->middleware(['authenticate', 'adminonly']);
// Assign an Employee as manager of other employee
Route::get('/employee/assignmanager/{manager_employeeID}/{subordinate_employeeID}', 'EmployeeController@assignManager')->middleware(['authenticate', 'adminonly']);
Route::get('/employee/verify/{employeeID}', 'EmployeeController@verifyEmployeRegistrations')->middleware(['authenticate', 'manageronly']);
Route::get('/employee/delete/{employeeID}', 'EmployeeController@deleteEmployeRegistrations')->middleware(['authenticate', 'manageronly']);
