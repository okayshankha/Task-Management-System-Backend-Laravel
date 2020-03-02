<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Task extends Model
{
    protected $table = 'task_details';
    protected $primaryKey = 'task_details_id';

    protected $casts = ['task_details_id' => 'string'];
}
