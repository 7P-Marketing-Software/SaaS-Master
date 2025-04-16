<?php

namespace App\Http\Controllers;

use App\Http\Traits\ResponsesTrait;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;

abstract class Controller
{
    use AuthorizesRequests, ResponsesTrait;
}
