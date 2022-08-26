<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use App\Models\User;
use PHPOpenSourceSaver\JWTAuth\Facades\JWTAuth;
use SendsPasswordResetEmails;
use Illuminate\Support\Facades\Mail;

class AuthController extends Controller
{

    public function __construct(){

       // $this->middleware('auth.role',['except'=>['login','register']]);
        
    }

    public function login(Request $request)
    {
        $request->validate([
            'email' => 'required|string|email',
            'password' => 'required|string',
        ]);

        $credentials = $request->only('email', 'password');
        
        if (!$token = JWTAuth::attempt(($credentials))) {
            return response()->json([
                'status' => 'error',
                'message' => 'Invalid login informations',
            ], 401);
        }

        $user = JWTAuth::user();
        
        return response()->json([
                'status' => 'success',
                'user' => $user,
                'authorisation' => [
                    'token' => $token,
                    'type' => 'bearer',
                ]
            ]);

    }

    public function register(Request $request){

        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users',
            'password' => 'required|string|min:6',
            'role' => 'required|string|max:24'
        ]);

        $user = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => Hash::make($request->password),
            'role' => $request->role
        ]);

        $token = Auth::login($user);

        return response()->json([
            'status' => 'success',
            'message' => 'User created successfully',
            'user' => $user,
            'authorisation' => [
                'token' => $token,
                'type' => 'bearer',
            ]
        ]);
    }

    public function logout()
    {
        Auth::logout();
        return response()->json([
            'status' => 'success',
            'message' => 'Successfully logged out',
        ]);
    }

    public function profil()
    {
        return response()->json([
            'status' => 'success',
            'user' => JWTAuth::user(),
        ]);
    }

    public function refresh()
    {
        return response()->json([
            'status' => 'success',
            'user' => JWTAuth::user(),
            'authorisation' => [
                'token' => JWTAuth::refresh(),
                'type' => 'bearer',
            ]
        ]);
    }

    public function sendResetTokenPassword(Request $request){

        $request->validate([
            'email'=> 'required|email|exists:users',
        ]);
        
        $email = $request->email;

        $reset_password_token = mt_rand(100000, 999999);

        $code = User::Where('email',$email)->first()->update([
            'reset_password_token'=>$request->reset_password_token,
        ]);
    }

}