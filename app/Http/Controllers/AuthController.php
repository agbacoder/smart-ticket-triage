<?php

namespace App\Http\Controllers;

use App\Services\AuthService;
use Illuminate\Http\Request;

class AuthController extends Controller
{
    protected AuthService $authService;

    public function __construct(AuthService $authService)
    {
        $this->authService = $authService;
    }

    public function register(Request $request)
    {
        $data = $request->validate([
            'name'     => 'required|string|max:255',
            'email'    => 'required|string|email|unique:users',
            'password' => 'required|string|min:6|confirmed',
        ]);

        $result = $this->authService->register($data);

        return response()->json([
            'status'  => 201,
            'data'    => $result,
        ], 201);
    }

    public function login(Request $request)
    {
        $data = $request->validate([
            'email'    => 'required|email',
            'password' => 'required|string|min:6',
        ]);

        $result = $this->authService->login($data);

        return response()->json([
            'status'  => 200,
            'data'    => $result,
        ], 200);
    }

    public function logout(Request $request)
    {
        $result = $this->authService->logout($request->user());

        return response()->json([
            'status'  => 200,
            'data'    => $result,
        ], 200);
    }
}
