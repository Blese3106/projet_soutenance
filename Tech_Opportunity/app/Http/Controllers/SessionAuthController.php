<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class SessionAuthController extends Controller
{
    public function login(Request $request)
    {
        $request->validate([
            "email" => "required|email",
            "password" => "required"
        ]);

        if (!Auth::attempt($request->only("email", "password"))) {
            return response()->json(["message" => "Identifiants invalides"], 401);
        }

        $request->session()->regenerate();

        return response()->json(["message" => "Session created"]);
    }
}
