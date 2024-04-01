<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\UserData;
use Illuminate\Http\Request;

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
            return response()->json(['token' => $token->plainTextToken, 'name'=> $user->user_data['name'], 'surname'=> $user->user_data['surname']], 202);
        }
        return response()->json(['message' => 'Login failed!'], 401);
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
            $user->user_data()->create([
                'name' => $data['name'],
                'surname' => $data['surname'],
            ]);
            $token = $user->createToken($data['email'])->plainTextToken;
            
            return response()->json(['token' => $token, 'name'=> $user->user_data['name'], 'surname'=> $user->user_data['surname']], 201);
        }
    }

    public function user(Request $request) {
        $user = auth()->user();
        $userData = $user->user_data; // Access the relationship using user_data(), not userData

        return response()->json($user);
    }

    public function logout(Request $request) {
        $user = auth()->user();
        $user->currentAccessToken()->delete();

        return response()->json(['message' => 'Logout successful!'], 200);
    }
    
}
