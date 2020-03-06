<?php

return [
    // Success Responses
    'success' => ['status' => 'success'],



    // Error Responses
    'error' => ['status' => 'failed', 'info' => 'Unknown Error Caught'],
    'error_404_parameter' => ['status' => 'failed', 'info' => 'Parameter(s) not found'],
    'error_404_token' => ['status' => 'failed', 'info' => 'Authorization token not provided'],
    'error_403_token' => ['status' => 'failed', 'info' => 'Unauthorized token provided'],
    'error_404_user_login' => ['status' => 'failed', 'info' => 'Incorrect user credentials'],
    'error_before_register_already_logged_in' => ['status' => 'failed', 'info' => 'Please logout first brfore registering'],

    'error_access_denied' => ['status' => 'failed', 'info' => 'Access denied'],

    'error_existing_username' =>  ['status' => 'failed', 'info' => 'Username is not available'],
    'error_existing_email' =>  ['status' => 'failed', 'info' => 'Email is not available'],
    'error_existing_mobile' =>  ['status' => 'failed', 'info' => 'Mobile is not available'],

    'error_already_logged_out' =>  ['status' => 'failed', 'info' => 'You have already logged out'],

    'error_404_task' => ['status' => 'failed', 'info' => 'Task not found'],
    'error_404_project' => ['status' => 'failed', 'info' => 'Project not found'],
    'error_404_employee' => ['status' => 'failed', 'info' => 'Employee not found'],

    'error_403_employee_is_not_assigned_to_project' => ['status' => 'failed', 'info' => 'Assign employee to the project first'],
    'error_400_employee_already_assigned' => ['status' => 'failed', 'info' => 'Employee is already assigned to the project'],
    'error_403_task_is_already_assigned' => ['status' => 'failed', 'info' => 'Task is already assigned to someone'],
    'error_403_parent_task_id_should_be_different' => ['status' => 'failed', 'info' => 'Parent task ID cannot be same as child task ID'],
    'error_401_associated_project_is_not_in_inprogress_state' => ['status' => 'failed', 'info' => 'Task associated project is not in active state'],
    'error_401_task_is_not_in_valid_state' => ['status' => 'failed', 'info' => 'Task is not in active state'],
    'error_401_parent_task_should_be_associated_with_same_project' => ['status' => 'failed', 'info' => 'Parent task must be associated with same project'],
    'error_same_maneger_is_already_assigned' => ['status' => 'failed', 'info' => 'Same manager is already assigned'],
    'error_employee_is_already_verified' => ['status' => 'failed', 'info' => 'Employee is already verified'],
    'error_employee_is_already_deleted' => ['status' => 'failed', 'info' => 'Employee is already deleted'],
    'error_403_employee_is_not_verified' => ['status' => 'failed', 'info' => 'Employee is not verified'],
    'error_403_employee_is_not_associated_with_project' => ['status' => 'failed', 'info' => 'Employee is not associated with this project'],
];
