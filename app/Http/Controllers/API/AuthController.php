<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
use Illuminate\Http\Request;
use App\Models\User;
use Illuminate\Support\Facades\Hash;


class AuthController extends Controller
{
    public function login(Request $request)
    {
        // Validate the request data
        $validator = Validator::make($request->all(), [
            'email' => 'required|email',
            'password' => 'required',
        ]);

        // If validation fails, return an error response
        if ($validator->fails()) {
            return response()->json(['message' => 'Validation failed', 'errors' => $validator->errors()], 422);
        }

        // Attempt to authenticate the user
        if (Auth::attempt(['email' => $request->email, 'password' => $request->password])) {
            $user = Auth::user();
            $token = $user->createToken('auth_token')->plainTextToken;

            return response()->json(['token' => $token , 'role' => $user->role , 'user_id' => $user->id, 'user_name' => $user->name  ]);
        }

        // If authentication fails, return an error response
        return response()->json(['message' => 'Invalid credentials'], 401);
    }
    public function register(Request $request)
    {
        // Validate the request data
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email',
            'password' => 'required|string|min:6',
        ]);

        // If validation fails, return an error response
        if ($validator->fails()) {
            return response()->json(['message' => 'Validation failed', 'errors' => $validator->errors()], 422);
        }

        // Create a new user
        $user = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'user_image'=>$request->image,
            'password' => Hash::make($request->password),
        ]);

        // Optionally, you can generate a token for the registered user
        $token = $user->createToken('auth_token')->plainTextToken;

        return response()->json(['user' => $user, 'token' => $token], 201);
    }
    
    public function logout(Request $request)
    {
        // Revoke all tokens associated with the authenticated user
        $request->user()->tokens()->delete();

        return response()->json(['message' => 'Successfully logged out']);
    }


    public function updateUser(Request $request, $id)
        {
            // Find the user by ID
            $user = User::find($id);

            // If the user doesn't exist, return an error response
            if (!$user) {
                return response()->json(['message' => 'User not found'], 404);
            }

            // Validate the request data
            $validator = Validator::make($request->all(), [
                'name' => 'string|max:255',
                'email' => 'email|unique:users,email,' . $id,
                'password' => 'string|min:6',
            ]);

            // If validation fails, return an error response
            if ($validator->fails()) {
                return response()->json(['message' => 'Validation failed', 'errors' => $validator->errors()], 422);
            }

            // Update user data if provided in the request
            if ($request->has('name')) {
                $user->name = $request->name;
            }
            if ($request->has('email')) {
                $user->email = $request->email;
            }
            if ($request->has('password')) {
                $user->password = Hash::make($request->password);
            }
            if ($request->has('user_image')) {
                $user->image_url = $request->user_image;
            }

            // Save the updated user
            $user->save();

            return response()->json(['message' => 'User updated successfully', 'user' => $user], 200);
        }

    public function updatePassword ( Request $request , $id){

        $request->validate([
            'current_password' => 'required',
            'new_password' => 'required|string|min:6',
        ]);

        // Get the currently authenticated user
        $user = $request->user();

        // Verify the current password
        if (!Hash::check($request->current_password, $user->password)) {
            return response()->json(['message' => 'The current password is incorrect.'], 422);
        }

        // Update the user's password
        $user->password = Hash::make($request->new_password);
        $user->save();

        return response()->json(['message' => 'Password updated successfully.']);
    }
}
