<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Role;
use Illuminate\Auth\Events\Registered;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rules;

class AuthController extends Controller
{
    /**
     * Register a new user
     */
    public function register(Request $request): JsonResponse
    {
        try {
            $validated = $request->validate([
                'name' => ['required', 'string', 'max:255'],
                'email' => ['required', 'string', 'email', 'max:255', 'unique:users,email'],
                'username' => ['required', 'string', 'max:100', 'unique:users,username'],
                'password' => ['required', 'confirmed', Rules\Password::defaults()],
            ]);

            // Get the "User" role
            $userRole = Role::where('name', 'User')->firstOrCreate(
                ['name' => 'User'],
                ['description' => 'Regular user']
            );

            // Create the user
            $user = User::create([
                'name' => $validated['name'],
                'email' => $validated['email'],
                'username' => $validated['username'],
                'password' => Hash::make($validated['password']),
                'role_id' => $userRole->id,
                'xp' => 0,
                'level' => 1,
                'badges' => [],
            ]);

            // Fire the Registered event
            event(new Registered($user));

            // Auto-login the user
            Auth::login($user);
            $request->session()->regenerate();

            return response()->json([
                'message' => 'User registered successfully',
                'success' => true,
                'user' => [
                    'id' => $user->id,
                    'name' => $user->name,
                    'email' => $user->email,
                    'username' => $user->username,
                    'xp' => $user->xp,
                    'level' => $user->level,
                    'badges' => $user->badges,
                ],
            ], 201);

        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json([
                'errors' => $e->errors(),
                'success' => false,
            ], 422);
        } catch (\Exception $e) {
            return response()->json([
                'errors' => ['general' => [$e->getMessage()]],
                'success' => false,
            ], 500);
        }
    }

    /**
     * Login user with email or username
     */
    public function login(Request $request): JsonResponse
    {
        try {
            $request->validate([
                'email' => ['required', 'string'],
                'password' => ['required', 'string'],
            ]);

            // Try to find user by email or username
            $user = User::where('email', $request->email)
                ->orWhere('username', $request->email)
                ->first();

            // Check if user exists and password is correct
            if (!$user || !Hash::check($request->password, $user->password)) {
                return response()->json([
                    'errors' => ['general' => ['Invalid email/username or password']],
                    'success' => false,
                ], 401);
            }

            // Login the user
            Auth::login($user, $request->remember ?? false);
            $request->session()->regenerate();

            return response()->json([
                'message' => 'Login successful',
                'success' => true,
                'user' => [
                    'id' => $user->id,
                    'name' => $user->name,
                    'email' => $user->email,
                    'username' => $user->username,
                    'xp' => $user->xp,
                    'level' => $user->level,
                    'badges' => $user->badges,
                ],
            ], 200);

        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json([
                'errors' => $e->errors(),
                'success' => false,
            ], 422);
        } catch (\Exception $e) {
            return response()->json([
                'errors' => ['general' => [$e->getMessage()]],
                'success' => false,
            ], 500);
        }
    }

    /**
     * Get authenticated user
     */
    public function me(Request $request): JsonResponse
    {
        $user = Auth::user();

        if (!$user) {
            return response()->json([
                'error' => 'Unauthenticated',
                'success' => false,
            ], 401);
        }

        return response()->json([
            'user' => [
                'id' => $user->id,
                'name' => $user->name,
                'email' => $user->email,
                'username' => $user->username,
                'bio' => $user->bio,
                'avatar_url' => $user->avatar_url,
                'country' => $user->country,
                'favourite_car' => $user->favourite_car,
                'xp' => $user->xp,
                'level' => $user->level,
                'badges' => $user->badges,
            ],
            'success' => true,
        ], 200);
    }

    /**
     * Logout user
     */
    public function logout(Request $request): JsonResponse
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return response()->json([
            'message' => 'Logged out successfully',
            'success' => true,
        ], 200);
    }

    /**
     * Update user profile
     */
    public function updateProfile(Request $request): JsonResponse
    {
        try {
            $user = Auth::user();

            if (!$user) {
                return response()->json([
                    'error' => 'Unauthenticated',
                    'success' => false,
                ], 401);
            }

            $validated = $request->validate([
                'name' => ['sometimes', 'string', 'max:255'],
                'bio' => ['sometimes', 'string', 'max:500'],
                'country' => ['sometimes', 'string', 'max:100'],
                'favourite_car' => ['sometimes', 'string', 'max:255'],
                'avatar_url' => ['sometimes', 'url'],
            ]);

            $user->update($validated);

            return response()->json([
                'message' => 'Profile updated successfully',
                'user' => [
                    'id' => $user->id,
                    'name' => $user->name,
                    'email' => $user->email,
                    'username' => $user->username,
                    'bio' => $user->bio,
                    'avatar_url' => $user->avatar_url,
                    'country' => $user->country,
                    'favourite_car' => $user->favourite_car,
                    'xp' => $user->xp,
                    'level' => $user->level,
                    'badges' => $user->badges,
                ],
                'success' => true,
            ], 200);

        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json([
                'errors' => $e->errors(),
                'success' => false,
            ], 422);
        } catch (\Exception $e) {
            return response()->json([
                'errors' => ['general' => [$e->getMessage()]],
                'success' => false,
            ], 500);
        }
    }

    /**
     * Change password
     */
    public function changePassword(Request $request): JsonResponse
    {
        try {
            $user = Auth::user();

            if (!$user) {
                return response()->json([
                    'error' => 'Unauthenticated',
                    'success' => false,
                ], 401);
            }

            $validated = $request->validate([
                'current_password' => ['required', 'string'],
                'password' => ['required', 'confirmed', Rules\Password::defaults()],
            ]);

            // Check current password
            if (!Hash::check($validated['current_password'], $user->password)) {
                return response()->json([
                    'errors' => ['current_password' => ['Current password is incorrect']],
                    'success' => false,
                ], 422);
            }

            // Update password
            $user->update(['password' => Hash::make($validated['password'])]);

            return response()->json([
                'message' => 'Password changed successfully',
                'success' => true,
            ], 200);

        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json([
                'errors' => $e->errors(),
                'success' => false,
            ], 422);
        } catch (\Exception $e) {
            return response()->json([
                'errors' => ['general' => [$e->getMessage()]],
                'success' => false,
            ], 500);
        }
    }
}
