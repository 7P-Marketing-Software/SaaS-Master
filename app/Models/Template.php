<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Template extends Model
{
    protected $fillable = [
        'application_name',
        'domain',
        'front_replicas',
        'back_replicas',
        'db_name',
        'db_user',
        'db_pass',
        'seeder_file'
    ];
}
