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
    public function register(Request $request): JsonResponse
    {
        try {
            $validated = $request->validate([
                'name'     => ['required', 'string', 'max:255'],
                'email'    => ['required', 'string', 'email', 'max:255', 'unique:users,email'],
                'username' => ['required', 'string', 'max:100', 'unique:users,username'],
                'password' => ['required', 'confirmed', Rules\Password::defaults()],
            ]);

            $userRole = Role::where('name', 'User')->firstOrCreate(
                ['name' => 'User'],
                ['description' => 'Regular user']
            );

            $user = User::create([
                'name'     => $validated['name'],
                'email'    => $validated['email'],
                'username' => $validated['username'],
                'password' => Hash::make($validated['password']),
                'role_id'  => $userRole->id,
            ]);

            event(new Registered($user));
            Auth::login($user);
            $request->session()->regenerate();

            return response()->json([
                'message' => 'User registered successfully',
                'success' => true,
                'user'    => [
                    'id'           => $user->id,
                    'name'         => $user->name,
                    'email'        => $user->email,
                    'username'     => $user->username,
                    'role_id'      => $user->role_id,
                    'level'        => $user->level(),
                    'total_points' => $user->totalPoints(),
                    'rank'         => $user->rank(),
                ],
            ], 201);

        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json(['errors' => $e->errors(), 'success' => false], 422);
        } catch (\Exception $e) {
            return response()->json(['errors' => ['general' => [$e->getMessage()]], 'success' => false], 500);
        }
    }

    public function login(Request $request): JsonResponse
    {
        try {
            $request->validate([
                'email'    => ['required', 'string'],
                'password' => ['required', 'string'],
            ]);

            $user = User::where('email', $request->email)
                ->orWhere('username', $request->email)
                ->first();

            if (!$user || !Hash::check($request->password, $user->password)) {
                return response()->json([
                    'errors'  => ['general' => ['Invalid email/username or password']],
                    'success' => false,
                ], 401);
            }

            Auth::login($user, $request->remember ?? false);
            $request->session()->regenerate();

            return response()->json([
                'message' => 'Login successful',
                'success' => true,
                'user'    => [
                    'id'           => $user->id,
                    'name'         => $user->name,
                    'email'        => $user->email,
                    'username'     => $user->username,
                    'role_id'      => $user->role_id,
                    'level'        => $user->level(),
                    'total_points' => $user->totalPoints(),
                    'rank'         => $user->rank(),
                ],
            ], 200);

        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json(['errors' => $e->errors(), 'success' => false], 422);
        } catch (\Exception $e) {
            return response()->json(['errors' => ['general' => [$e->getMessage()]], 'success' => false], 500);
        }
    }

    public function me(Request $request): JsonResponse
    {
        $user = Auth::user();

        if (!$user) {
            return response()->json(['error' => 'Unauthenticated', 'success' => false], 401);
        }

        return response()->json([
            'success' => true,
            'user'    => [
                'id'            => $user->id,
                'name'          => $user->name,
                'email'         => $user->email,
                'username'      => $user->username,
                'role_id'       => $user->role_id,
                'bio'           => $user->bio,
                'avatar_url'    => $user->avatar_url,
                'country'       => $user->country,
                'favourite_car' => $user->favourite_car,
                'level'         => $user->level(),
                'total_points'  => $user->totalPoints(),
                'rank'          => $user->rank(),
                'level_color'   => $user->levelColor(),
            ],
        ], 200);
    }

    public function logout(Request $request): JsonResponse
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return response()->json(['message' => 'Logged out successfully', 'success' => true], 200);
    }

    public function updateProfile(Request $request): JsonResponse
    {
        try {
            $user = Auth::user();

            if (!$user) {
                return response()->json(['error' => 'Unauthenticated', 'success' => false], 401);
            }

            $validated = $request->validate([
                'name'         => ['sometimes', 'string', 'max:255'],
                'bio'          => ['sometimes', 'string', 'max:500'],
                'country'      => ['sometimes', 'string', 'max:100'],
                'favourite_car'=> ['sometimes', 'string', 'max:255'],
                'avatar_url'   => ['sometimes', 'url'],
            ]);

            $user->update($validated);

            return response()->json([
                'message' => 'Profile updated successfully',
                'success' => true,
                'user'    => [
                    'id'            => $user->id,
                    'name'          => $user->name,
                    'email'         => $user->email,
                    'username'      => $user->username,
                    'role_id'       => $user->role_id,
                    'bio'           => $user->bio,
                    'avatar_url'    => $user->avatar_url,
                    'country'       => $user->country,
                    'favourite_car' => $user->favourite_car,
                    'level'         => $user->level(),
                    'total_points'  => $user->totalPoints(),
                    'rank'          => $user->rank(),
                ],
            ], 200);

        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json(['errors' => $e->errors(), 'success' => false], 422);
        } catch (\Exception $e) {
            return response()->json(['errors' => ['general' => [$e->getMessage()]], 'success' => false], 500);
        }
    }

    public function changePassword(Request $request): JsonResponse
    {
        try {
            $user = Auth::user();

            if (!$user) {
                return response()->json(['error' => 'Unauthenticated', 'success' => false], 401);
            }

            $validated = $request->validate([
                'current_password' => ['required', 'string'],
                'password'         => ['required', 'confirmed', Rules\Password::defaults()],
            ]);

            if (!Hash::check($validated['current_password'], $user->password)) {
                return response()->json([
                    'errors'  => ['current_password' => ['Current password is incorrect']],
                    'success' => false,
                ], 422);
            }

            $user->update(['password' => Hash::make($validated['password'])]);

            return response()->json(['message' => 'Password changed successfully', 'success' => true], 200);

        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json(['errors' => $e->errors(), 'success' => false], 422);
        } catch (\Exception $e) {
            return response()->json(['errors' => ['general' => [$e->getMessage()]], 'success' => false], 500);
        }
    }
}