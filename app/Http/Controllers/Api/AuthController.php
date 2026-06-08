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
            'device_name' => 'nullable|string',
        ]);

        $user = User::where('email', $request->email)->first();

        if (!$user || !Hash::check($request->password, $user->password)) {
            return $this->errorResponse('Invalid credentials.', 401);
        }

        // Verify if user is either a teacher or a parent
        if (!$user->hasAnyRole(['teacher', 'parent'])) {
            return $this->errorResponse('Access denied. You do not have permissions to access the mobile portals.', 403);
        }

        $role = $user->roles->first()?->name;
        $deviceName = $request->device_name ?? 'mobile_app';
        $token = $user->createToken($deviceName)->plainTextToken;

        return $this->successResponse([
            'token' => $token,
            'user' => [
                'id' => $user->id,
                'name' => $user->name,
                'email' => $user->email,
                'role' => $role,
                'school' => $user->school ? [
                    'id' => $user->school->id,
                    'name' => $user->school->name,
                    'slug' => $user->school->slug,
                ] : null,
            ]
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

        return $this->successResponse([
            'id' => $user->id,
            'name' => $user->name,
            'email' => $user->email,
            'role' => $role,
            'school' => $user->school ? [
                'id' => $user->school->id,
                'name' => $user->school->name,
                'slug' => $user->school->slug,
            ] : null,
        ]);
    }
}
