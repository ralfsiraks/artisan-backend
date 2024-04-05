<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
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

    public function getUser(Request $request) {
        $user = auth()->user();
        $userData = $user->user_data; // Access the relationship using user_data

        return response()->json($user, 200);
    }

    public function updateUser(Request $request) {
        $user = auth()->user();
        $userData = $user->user_data; // Access the relationship using user_data
        
        // Get the data from the request body
        $requestData = $request->all();
    
        // Update user attributes based on the request data
        foreach ($requestData as $key => $value) {
            // Check if the attribute exists on the user model and update it
            if (isset($user->$key)) {
                $user->$key = $value;
            } 
            // Check if the user_data exists and if the attribute exists on the user_data model, then update it
            elseif ($userData && isset($userData->$key)) {
                $userData->$key = $value;
            }
        }
    
        // Save the updated user and user_data
        $user->save();
        if ($userData) {
            $userData->save();
        }
    
        return response()->json($userData, 200);
    }

    public function logout(Request $request) {
        $user = auth()->user();
        $user->currentAccessToken()->delete();

        return response()->json(['message' => 'Logout successful!'], 200);
    }

    public function updatePassword(Request $request) {
        // Validate the incoming request data
        $request->validate([
            'oldPassword' => 'required|string|min:8',
            'newPassword' => 'required|string|min:8', 
        ]);
    
        // Get the authenticated user
        $user = auth()->user();
    
        // Check if the current password matches the user's stored password
        if (!Hash::check($request->oldPassword, $user->getAuthPassword())) {
            return response()->json(['message' => 'current password incorrect'], 400);
        }
    
        // Update the user's password
        $user->password = $request->newPassword;
        $user->save();
    
        return response()->json(['message' => 'updated'], 200);
    }
    
}
