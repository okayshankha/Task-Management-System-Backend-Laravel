<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class LoginAccessModel extends Model
{
    protected $table =  'login_access';
    protected $primaryKey = 'login_access_id';

    protected $hidden = [
        'password',
    ];
}
