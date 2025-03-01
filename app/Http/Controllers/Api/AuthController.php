<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;

class AuthController extends Controller
{
    public function register()
    {
        return view('pages.register');
    }

    public function registerpost(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'username' => 'required|string|unique:users,username|regex:/^[a-zA-Z0-9._]+$/|min:3',
            'full_name' => 'required|string',
            'password' => 'required|string|min:6',
            'bio' => 'required|string',
            'is_private' => 'boolean'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'message' => 'Invalid field',
                'errors' => $validator->errors()
            ], 422);
        }

        $validated = $validator->validated();

        $user = User::create([
            'username' => $validated['username'],
            'full_name' => $validated['full_name'],
            'password' => Hash::make($validated['password']),
            'bio' => $validated['bio'],
            'is_private' => $request->boolean('is_private')
        ]);

        $token = $user->createToken('facegram')->plainTextToken;

        return response()->json([
            'message' => 'Register success',
            'token' => $token,
            'data' => $user,
            'redirect' => url()->route('home')
        ], 201);
    }

    public function login()
    {
        return view('pages.index');
    }

    public function loginpost(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'username' => 'required|string',
            'password' => 'required|string',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'message' => "Invalid credentials",
                'errors' => $validator->errors()
            ], 422);
        }

        $user = User::where('username', $request->username)->first();

        if (!$user || !Hash::check($request->password, $user->password)) {
            return response()->json([
                'message' => "Wrong username or password"
            ], 401);
        }

        $user->tokens()->delete();
        $token = $user->createToken('facegram')->plainTextToken;

        return response()->json([
            'message' => 'Login success',
            'token' => $token,
            'data' => $user,
        ]);

        return redirect()->route('home');


    }

    public function logout(Request $request)
    {
        $request->user()->currentAccessToken()->delete();

        return response()->json([
            'message' => 'Logout success'
        ]);
    }
}
