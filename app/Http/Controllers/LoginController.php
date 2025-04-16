<?php

namespace App\Http\Controllers;

use App\Models\SystemOwner;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class LoginController extends Controller
{
    public function login(Request $request)
    {
        $request->validate([
            'phone'=>'required|string',
            'password'=>'required|string',
        ]);

        $owner = SystemOwner::where('phone', $request->input('phone'))->first();

        if (!$owner || !Hash::check($request->input('password'), $owner->password)) {
            return $this->respondError(null,'Invalid Credentials.');
        }
        $token = $owner->createToken('Owner Access Token')->plainTextToken;
        $owner->token=$token;

        return $this->respondOk($owner, 'Login Successfully.');

    }
}
