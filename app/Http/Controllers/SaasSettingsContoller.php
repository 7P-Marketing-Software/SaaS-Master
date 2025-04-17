<?php

namespace App\Http\Controllers;

use App\Models\SaasSettings;
use Illuminate\Http\Request;

class SaasSettingsContoller extends Controller
{
    public function index()
    {
        $saasSettings = SaasSettings::paginate();
        return $this->respondOk($saasSettings, 'SaaS settings retrieved successfully');
    }
    
    public function show($applicationName)
    {
        $saasSettings = SaasSettings::where('application_name', $applicationName)->first();
        if (!$saasSettings) {
            return $this->respondError(null, 'Application not found');
        }
        return $this->respondOk($saasSettings, 'SaaS settings retrieved successfully');
    }

    public function updateSettings(Request $request,$applicationName)
    {
        $saasSettings = SaasSettings::where('application_name', $applicationName)->first();
        if (!$saasSettings) {
            return $this->respondError(null, 'Application not found');
        }
        $request->validate([
            'is_active' => 'nullable|boolean',
            'is_maintenance' => 'nullable|boolean',
            'interactions' => 'nullable|boolean',
            'chatBot' => 'nullable|boolean',
            'questions_community' => 'nullable|boolean',
            'quotes' => 'nullable|boolean',
            'blog' => 'nullable|boolean',
            'video_setting' => 'nullable|in:server,link,youtube,all',
            'gamafications' => 'nullable|boolean',
            'categories' => 'nullable|boolean',
            'attendance_system' => 'nullable|boolean',
        ]);
    
        $saasSettings->update($request->only([
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
            'attendance_system',
        ]));
    
        return $this->respondOk($saasSettings, 'Settings updated successfully');
    }
}
