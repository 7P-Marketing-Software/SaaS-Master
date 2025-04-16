<?php

use App\Http\Controllers\TemplateController;
use App\Http\Controllers\LoginController;
use App\Http\Controllers\SaasSettingsContoller;
use Illuminate\Support\Facades\Route;


Route::post('/system-owner/login', [LoginController::class, 'login']);

Route::middleware(['auth:sanctum'])->group(function () {
    Route::post('/create-template', [TemplateController::class, 'createTemplate']);
   
    Route::get('/all-settings', [SaasSettingsContoller::class, 'index']);
    Route::post('/show/{applicationName}/settings', [SaasSettingsContoller::class, 'show']);
    Route::post('/update-settigs/{applicationName}', [SaasSettingsContoller::class, 'updateSettings']);
}); 