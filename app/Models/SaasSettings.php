<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SaasSettings extends Model
{
    protected $fillable = [
        'application_name',
        'is_active',
        'is_maintenance',
        'interactions',
        'chatBot',
        'questions_community',
        'quotes',
        'blog',
        'video_setting',
        'gamafications',
        'categories',
        'attendance_system'
    ];
    protected $casts = [
        'is_active' => 'boolean',
        'is_maintenance' => 'boolean',
        'interactions' => 'boolean',
        'chatBot' => 'boolean',
        'questions_community' => 'boolean',
        'quotes' => 'boolean',
        'blog' => 'boolean',
        'gamafications' => 'boolean',
        'categories' => 'boolean'
        'attendance_system' => 'boolean',
    ];
}
