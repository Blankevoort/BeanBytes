<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use App\Http\Requests\UserLoginRequest;

class AuthController extends Controller
{
    public function checkStatus(Request $request)
    {
        $emailPhone = $request->emailPhone;
        $isEmail = filter_var($emailPhone, FILTER_VALIDATE_EMAIL);

        $userExists = User::where('email', $emailPhone)
            ->orWhere('phone', $emailPhone)
            ->exists();

        return response()->json([
            'status' => $userExists ? 'login' : 'register',
            $isEmail ? 'email' : 'phone' => "",
        ], 200);
    }

    public function checkDuplicate(Request $request)
    {
        $userExistsEmail = User::where('email', $request->emailPhone)->exists();
        $userExistsPhone = User::where('phone', $request->emailPhone)->exists();

        if ($userExistsEmail || $userExistsPhone) {
            return response()->json([
                'status' => 409,
                'email' => $userExistsEmail ? '' : null,
                'phone' => $userExistsPhone ? '' : null,
            ], 200);
        }

        return response()->json(['status' => 'ok'], 200);
    }

    public function register(Request $request)
    {
        $validated = $request->validate([
            'phone' => 'required|unique:users,phone',
            'email' => 'required|email|unique:users,email',
            'password' => 'required|string|min:6',
        ]);

        $randomNumber = rand(10000, 99999);

        // 'fullname' => "User{$randomNumber} Fullname",
        // 'username' => "User{$randomNumber} Username",
        // 'jobTitle' => "User{$randomNumber} jobTitle",
        $user = User::create([
            'phone' => $validated['phone'],
            'email' => $validated['email'],
            'password' => Hash::make($validated['password']),
        ]);

        return response()->json([
            'user' => $user,
            'token' => $user->createToken('API Token')->plainTextToken,
        ], 201);
    }

    public function login(UserLoginRequest $request)
    {
        $credentials = [
            filter_var($request->emailPhone, FILTER_VALIDATE_EMAIL) ? 'email' : 'phone' => $request->emailPhone,
            'password' => $request->password,
        ];

        if (!Auth::attempt($credentials)) {
            return response()->json(['message' => 'Credentials do not match'], 401);
        }

        $user = Auth::user();

        return response()->json([
            'user' => $user,
            'token' => $user->createToken('API Token')->plainTextToken,
        ]);
    }

    public function logout()
    {
        Auth::user()->currentAccessToken()->delete();

        return response()->json([
            'message' => 'You have successfully logged out'
        ]);
    }
}
