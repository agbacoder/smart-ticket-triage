<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;

class AuthService
{
    public function register(array $data): array
    {
        $user = User::create([
            'name'     => $data['name'],
            'email'    => $data['email'],
            'password' => Hash::make($data['password']),
        ]);

        return [
            'message' => 'Registration successful',
        ];
    }

    public function login(array $credentials): array
    {
        if (!Auth::attempt($credentials)) {
            abort(401, 'Invalid login details');
        }

        $user  = Auth::user();
        $token = $user->createToken('authToken')->accessToken;

        return [
            'user'  => $user,
            'token' => $token,
        ];
    }

    public function logout(User $user): array
    {
        $user->token()->revoke();
        return [
            'message' => 'Logged out successfully',
        ];
    }
}
