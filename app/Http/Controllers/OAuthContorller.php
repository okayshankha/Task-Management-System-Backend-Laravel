<?php

namespace App\Http\Controllers;

use App\Employee;
use App\LoginAccessModel;
use App\TokenModel;
use App\Role;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Hash;

class OAuthContorller extends Controller
{
    function register(Request $request)
    {
        $token = $request->token;
        $status = 200;
        if ($token && $token->status == config('GlobalValues.tokenValid')) {
            // If already logged in
            $status = 400;
            return response()->json(config('JsonResponse.error_before_register_already_logged_in'))->setStatusCode($status);
        } else {
            // If not logged in

            $mandatory_fields = ['username', 'password', 'fname', 'lname', 'email', 'mobile', 'address'];

            foreach ($mandatory_fields as $value) {
                if (!$request->input($value)) {
                    $status = 400;
                    $data = config('JsonResponse.error_404_parameter');
                    $data['missing_field'] = $value;
                    return response()->json($data)->setStatusCode($status);
                }
            }

            $username = $request->input('username');
            $password = $request->input('password');

            $fname = $request->input('fname');
            $mname = $request->input('mname');
            $lname = $request->input('lname');
            $email = $request->input('email');
            $mobile = $request->input('mobile');
            $address = $request->input('address');

            if ($this->existingUsername($username)) {
                $status = 403;
                $this->data = config('JsonResponse.error_existing_username');
            } elseif ($this->existingEmail($email)) {
                $status = 403;
                $this->data = config('JsonResponse.error_existing_email');
            } elseif ($this->existingMobile($mobile)) {
                $status = 403;
                $this->data = config('JsonResponse.error_existing_mobile');
            } else {
                $loginAccessModel = new LoginAccessModel;
                $loginAccessModel->username = $username;
                $loginAccessModel->password = Hash::make($password);
                $loginAccessModel->role_id = 2;
                $loginAccessModel->save();

                $loginAccessModel = LoginAccessModel::where('username', $username)->get()->first();

                $employee_id = null;

                if ($loginAccessModel) {
                    while (Employee::find($employee_id = config('GlobalValues.emoloyeeID_prefix') . $this->generateGUID()));
                    $employee = new Employee;
                    $employee->employee_id = $employee_id;
                    $employee->login_access_id = $loginAccessModel->login_access_id;
                    $employee->fname = $fname;
                    $employee->mname = $mname;
                    $employee->lname = $lname;
                    $employee->email = $email;
                    $employee->mobile = $mobile;
                    $employee->address = $address;

                    $employee->save();
                    $this->data = config('JsonResponse.success');
                } else {
                    $this->data = config('JsonResponse.error');
                }
            }
        }
        return response()->json($this->data)->setStatusCode($status);
    }

    function login(Request $request)
    {
        $status = 200;
        $username = $request->input('username');
        $password = $request->input('password');

        if (!$username || !$password) {
            $this->data = config('JsonResponse.error_404_parameter');
            $status = 400;
        } else {
            $employee = LoginAccessModel::where('username', $username)->get()->first();


            if ($employee && Hash::check($password, $employee->password)) {
                if ($employee->status != config('GlobalValues.employeeValid')) {
                    return response()->json(config('JsonResponse.error_403_employee_is_not_verified'));
                    $status = 401;
                }
                $role = Role::find($employee->role_id);
                $role = $role ? $role->name : '';
                $hasToken = $this->hasToken($employee->login_access_id);
                if ($hasToken) {
                    $this->data = config('JsonResponse.success');
                    $this->data['role'] = $role;
                    $this->data['token'] = $hasToken;
                } else {
                    $token = new TokenModel();
                    $token->login_access_id = $employee->login_access_id;
                    $token->token = Str::random(50);
                    $token->save();
                    $this->data = config('JsonResponse.success');
                    $this->data['role'] = $role;
                    $this->data['token'] = $token->token;
                }
            } else {
                $this->data = config('JsonResponse.error');
                $this->data['info'] = "Invalid user credentials";
            }
        }
        return response()->json($this->data)->setStatusCode($status);
    }

    function logout(Request $request)
    {
        $token = $request->token;
        if ($token) {
            if ($token->status == config('GlobalValues.tokenInvalid')) {
                return response()->json(config('JsonResponse.error_already_logged_out'));
            } else {
                $token->status = config('GlobalValues.tokenInvalid');
                $token->save();
                return response()->json(config('JsonResponse.success'));
            }
        } else {
            return response()->json(config('JsonResponse.error_403_token'));
        }
    }

    private function hasToken($login_access_id)
    {
        $token = TokenModel::where('login_access_id', $login_access_id)->where('status', config('GlobalValues.tokenValid'))->get();
        if ($token->count() > 0) {
            return $token->first()->token;
        } else {
            return null;
        }
    }

    /**
     * Returns true if the username already exists
     */
    private function existingUsername($username)
    {
        $employee = LoginAccessModel::where('username', $username)->get()->first();
        if ($employee) {
            return true;
        } else {
            return false;
        }
    }

    /**
     * Returns true if the email already exists
     */
    private function existingEmail($email)
    {
        $employee = Employee::where('email', $email)->get()->first();
        if ($employee) {
            return true;
        } else {
            return false;
        }
    }

    /**
     * Returns true if the mobile already exists
     */
    private function existingMobile($email)
    {
        $employee = Employee::where('mobile', $email)
            ->orWhere('mobile2', $email)
            ->get()
            ->first();
        if ($employee) {
            return true;
        } else {
            return false;
        }
    }
}
