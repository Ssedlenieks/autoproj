<?php

namespace App\Http\Controllers\Auth;

use App\Models\User;
use App\Models\Role;
use Illuminate\Auth\Events\Registered;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules;

class RegisteredUserController {
  public function store(Request $request): JsonResponse {
    $validated = $request->validate([
      'name' => ['required', 'string', 'max:255'],
      'email' => ['required', 'string', 'email', 'max:255', 'unique:users,email'],
      'username' => ['required', 'string', 'max:100', 'unique:users,username'],
      'password' => ['required', 'confirmed', Rules\Password::defaults()],
    ]);

    $userRole = Role::where('name', 'User')->first();

    $user = User::create([
      'name' => $validated['name'],
      'email' => $validated['email'],
      'username' => $validated['username'],
      'password' => Hash::make($validated['password']),
      'role_id' => $userRole->id,
    ]);

    event(new Registered($user));

    return response()->json([
      'message' => 'User registered successfully',
      'user' => [
        'id' => $user->id,
        'name' => $user->name,
        'email' => $user->email,
        'username' => $user->username,
      ],
    ], 201);
  }
}
