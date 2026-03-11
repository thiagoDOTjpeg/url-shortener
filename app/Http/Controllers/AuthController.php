<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;

class AuthController extends Controller
{
    public function register(Request $request) {
        $data = $request->validate([
            "name" => "required|string|max:255",
            "email" => "required|string|email|max:255|unique:users",
            "password" => "required|string|min:8|confirmed",
        ]);

        $user = User::create([
            "name" => $data["name"],
            "email" => $data["email"],
            "password" => Hash::make($data["password"]),
        ]);

        $token = $user->createToken("auth_token")->plainTextToken;


        return response()->json([
            "message" => "User registered successfully",
            "token" => $token,
            "user" => $user,
        ],201);
    }

    public function me(Request $request)
    {
        return response()->json($request->user());
    }

}
