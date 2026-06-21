<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Traits\ApiResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class AuthController extends Controller
{
    use ApiResponse;

    /**
     * Authenticate user and return token.
     */
    public function login(Request $request): JsonResponse
    {
        $request->validate([
            'email' => 'required|email',
            'password' => 'required',
            'role' => 'required|string',
            'device_info' => 'nullable|string',
            'device_type' => 'nullable|string',
            'device_os_version' => 'nullable|string',
            'fcm_token' => 'nullable|string',
        ]);

        $user = User::where('email', $request->email)->first();

        if (!$user || !Hash::check($request->password, $user->password)) {
            return $this->errorResponse('Invalid credentials.', 401);
        }

        // Map input role to Spatie roles
        $roleInput = $request->role;
        $mappedRoles = [];
        if (strcasecmp($roleInput, 'Administrator') === 0) {
            $mappedRoles = ['super-admin', 'school-admin'];
        } elseif (strcasecmp($roleInput, 'Teacher') === 0) {
            $mappedRoles = ['teacher'];
        } elseif (strcasecmp($roleInput, 'Student') === 0) {
            $mappedRoles = ['student'];
        } elseif (strcasecmp($roleInput, 'Parent') === 0) {
            $mappedRoles = ['parent'];
        } else {
            return $this->errorResponse('Invalid role specified.', 422);
        }

        if (!$user->hasAnyRole($mappedRoles)) {
            return $this->errorResponse('Access denied. You do not have permissions for this role.', 403);
        }

        // Update user device info
        $user->update([
            'device_info' => $request->device_info,
            'device_type' => $request->device_type,
            'device_os_version' => $request->device_os_version,
            'fcm_token' => $request->fcm_token,
        ]);

        $role = $user->roles->first()?->name;
        // Map back to display role
        $displayRole = $roleInput;
        if ($role === 'super-admin' || $role === 'school-admin') {
            $displayRole = 'Administrator';
        } elseif ($role === 'teacher') {
            $displayRole = 'Teacher';
        } elseif ($role === 'student') {
            $displayRole = 'Student';
        } elseif ($role === 'parent') {
            $displayRole = 'Parent';
        }

        $deviceName = $request->device_info ?? 'mobile_app';
        $token = $user->createToken($deviceName)->plainTextToken;

        return $this->successResponse([
            '_id' => "usr_" . $user->id,
            'user_name' => $user->user_name,
            'first_name' => $user->first_name,
            'last_name' => $user->last_name,
            'email' => $user->email,
            'user_type' => $user->user_type,
            'role' => $displayRole,
            'created_at' => $user->created_at ? $user->created_at->toISOString() : null,
            'resetToken' => '',
            'device_info' => $user->device_info,
            'device_type' => $user->device_type,
            'device_os_version' => $user->device_os_version,
            'token' => $token,
        ], 'Login successful.');
    }

    /**
     * Revoke authenticated user's current token.
     */
    public function logout(Request $request): JsonResponse
    {
        $request->user()->currentAccessToken()->delete();

        return $this->successResponse(null, 'Logged out successfully.');
    }

    /**
     * Get authenticated user profile details.
     */
    public function me(Request $request): JsonResponse
    {
        $user = $request->user();
        $role = $user->roles->first()?->name;

        // Map back to display role
        $displayRole = 'Student';
        if ($role === 'super-admin' || $role === 'school-admin') {
            $displayRole = 'Administrator';
        } elseif ($role === 'teacher') {
            $displayRole = 'Teacher';
        } elseif ($role === 'parent') {
            $displayRole = 'Parent';
        }

        return $this->successResponse([
            '_id' => "usr_" . $user->id,
            'user_name' => $user->user_name,
            'first_name' => $user->first_name,
            'last_name' => $user->last_name,
            'email' => $user->email,
            'user_type' => $user->user_type,
            'role' => $displayRole,
            'school' => $user->school ? [
                'id' => $user->school->id,
                'name' => $user->school->name,
                'slug' => $user->school->slug,
            ] : null,
        ]);
    }

    /**
     * Update authenticated user's FCM device token.
     */
    public function updateDeviceToken(Request $request): JsonResponse
    {
        $request->validate([
            'fcm_token' => 'required|string',
        ]);

        $request->user()->update([
            'fcm_token' => $request->fcm_token,
        ]);

        return $this->successResponse(null, 'Device token updated successfully.');
    }
}
