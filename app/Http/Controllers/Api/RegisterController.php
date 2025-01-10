<?php

namespace App\Http\Controllers\Api;

use App\Helpers\ResponseHelper;
use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;

class RegisterController extends Controller
{
    public function register(Request $request): JsonResponse
    {
        Log::info('Register method called');

        // Validate the request
        $request->validate([
            'name' => 'required|string|max:100',
            'email' => 'required|email|unique:users,email',
            'password' => 'required|min:6|max:10',
        ]);

        // Create and save the user
        $user = new User();
        $user->name = $request->name;
        $user->email = $request->email;
        $user->password = Hash::make($request->password);
        $user->save();

        // Create an access token
        $token = $user->createToken('Company')->plainTextToken;

        // Return a successful response with the token and PIN
        return ResponseHelper::success([
            'accept_token' => $token,
        ]);
    }
    public function login(Request $request)
    {
        // Validate the request data
        $credentials = $request->validate([
            'email' => 'required|email',      // Make email required
            'password' => 'required|string|min:6',  // Make password required
        ]);

        // Check if email and password are provided
        if (!Auth::attempt($credentials)) {
            return ResponseHelper::unauthenticated('Invalid credentials');
        }

        // Retrieve the authenticated user
        $user = Auth::user();

        // Generate an API token for the user (optional)
        $token = $user->createToken('Company')->plainTextToken;

        return response()->json([
            'message' => 'Login successful',
            'token' => $token,
        ]);
    }

    public function logout(Request $request)
    {
        $request->user()->currentAccessToken()->delete();

        return ResponseHelper::success([], 'Successfully logged out');
    }

}
