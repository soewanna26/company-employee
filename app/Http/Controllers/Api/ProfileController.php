<?php

namespace App\Http\Controllers\Api;

use App\Helpers\ResponseHelper;
use App\Http\Controllers\Controller;
use App\Http\Resources\ProfileResource;
use App\Models\User;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;

class ProfileController extends Controller
{
    public function index()
    {
        $profile = Auth::user();

        if ($profile) {
            return ResponseHelper::success(new ProfileResource($profile), "Profile retrieved successfully");
        } else {
            return ResponseHelper::notFound("Profile not found");
        }
    }

    public function update(Request $request)
    {
        $profile = Auth::user();

        if (!$profile) {
            return redirect()->back()->with('error', 'User not found');
        }

        $validator = Validator::make($request->all(), [
            'name' => 'required|min:3',
            'email' => 'required|email|unique:users,email,' . $profile->id,
        ]);

        if ($validator->fails()) {
            return ResponseHelper::validationError('Validation error', $validator->errors());
        }

        DB::beginTransaction();
        try {
            $profile->name = $request->input('name');
            $profile->email = $request->input('email');
            $profile->phone = $request->input('phone', $profile->phone); // Use existing phone if not provided
            $profile->save();

            DB::commit();
            return ResponseHelper::success(new ProfileResource($profile), "Profile updated successfully");
        } catch (Exception $e) {
            DB::rollBack();
            return ResponseHelper::internalServerError($e->getMessage());
        }
    }

    public function changePassword(Request $request)
    {
        $profile = Auth::user();

        // Validate the request
        $validator = Validator::make($request->all(), [
            'current_password' => 'required',
            'new_password' => 'required|min:5',
            'new_password_confirmation' => 'required|same:new_password',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => 'error',
                'message' => 'Validation error',
                'errors' => $validator->errors()
            ], 422);
        }

        // Check if the current password matches
        if (!Hash::check($request->current_password, $profile->password)) {
            return response()->json([
                'status' => 'error',
                'message' => 'Current password is incorrect'
            ], 400);
        }

        // Update the password
        try {
            $profile->password = Hash::make($request->new_password);
            $profile->save();

            return response()->json([
                'status' => 'success',
                'message' => 'Password changed successfully'
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Failed to change password'
            ], 500);
        }
    }

    public function destroy(Request $request)
    {
        $profile = Auth::user();
        // Delete the profile
        try {
            $profile->delete();

            return response()->json([
                'status' => 'success',
                'message' => 'Profile deleted successfully'
            ], 200);
        } catch (\Exception $e) {
            Log::error('Failed to delete profile: ' . $e->getMessage());
            return response()->json([
                'status' => 'error',
                'message' => 'Failed to delete profile'
            ], 500);
        }
    }
}
