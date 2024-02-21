<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class UserController extends Controller
{
    public function login(Request $request) {
        $data = $request->validate([
            'email' => 'required|email',
            'password' => 'required|min:8',
        ]);

        if (auth()->attempt($data)) {
            $user = User::where('email', $data['email'])->first();
            $token = $user->createToken($data['email']);
            return response()->json(['token' => $token->plainTextToken], 202);
        }
        return response()->json(['message' => 'Login unsuccessful!'], 401);
    }

    public function register(Request $request) {
        $data = $request->validate([
            'name' => 'required',
            'surname' => 'required',
            'email' => 'required|email',
            'password' => 'required|min:8'
        ]);

        $user  = User::where('email', $data['email'])->exists();
        
        if ($user) {
            return response()->json(['message' => 'An account with this email already exists!'], 409);
        } else {
            $user = User::create([
                'email' => $data['email'],
                'password' => $data['password'],
                'email_verified_at' => now(),
            ]);
            $token = $user->createToken($data['email'])->plainTextToken;
            return response()->json(['token' => $token], 201);
        }
    }

    public function user(Request $request) {
        $user = auth()->user();
        return response()->json($user);
    }

    public function logout(Request $request) {
        $user = auth()->user();
        $user->currentAccessToken()->delete();
        return response()->json(['message' => 'Logout successful!'], 200);
    }
    
}
