<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use App\Models\User;
use Exception;

class AuthController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth:api', ['except' => ['login', 'register']]);
    }

    public function login(Request $request)
    {
        try {
            $validator = Validator::make($request->all(), [
                'email' => 'required|string|email',
                'password' => 'required|string',
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'Message' => 'Error de validación',
                    'Errors' => $validator->errors()
                ], 422);
            }

            $credentials = $request->only('email', 'password');

            if (!$token = Auth::attempt($credentials)) {
                return response()->json([
                    'Message' => 'No autorizado',
                    'Error' => 'Email o contraseña incorrectos'
                ], 401);
            }

            $user = Auth::user();

            return response()->json([
                'access_token' => $token,
                'token_type' => 'bearer',
                'expires_in' => env('JWT_TTL') * 60,
                'user' => $user,
            ]);
        } catch (Exception $e) {
            return response()->json([
                'Message' => 'Error al intentar iniciar sesión',
                'Debug' => $e->getMessage()
            ], 500);
        }
    }

    public function register(Request $request)
    {
        try {
            $validator = Validator::make($request->all(), [
                'name' => 'required|string|max:255',
                'email' => 'required|string|email|max:255|unique:users',
                'password' => 'required|string|min:6',
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'Message' => 'Error de validación',
                    'Errors' => $validator->errors()
                ], 422);
            }

            $user = User::create([
                'name' => $request->name,
                'email' => $request->email,
                'password' => Hash::make($request->password)
            ]);

            return response()->json([
                'Message' => "Usuario registrado correctamente",
                'User' => $user,
            ], 201);
        } catch (Exception $e) {
            return response()->json([
                'Message' => 'Error al registrar el usuario',
                'Debug' => $e->getMessage()
            ], 500);
        }
    }

    public function me()
    {
        try {
            return response()->json(Auth::user());
        } catch (Exception $e) {
            return response()->json([
                'Message' => 'Error al obtener el usuario autenticado',
                'Debug' => $e->getMessage()
            ], 500);
        }
    }

    public function logout()
    {
        try {
            Auth::logout();
            return response()->json([
                'Message' => 'Sesión cerrada correctamente',
            ]);
        } catch (Exception $e) {
            return response()->json([
                'Message' => 'Error al cerrar sesión',
                'Debug' => $e->getMessage()
            ], 500);
        }
    }

    public function refresh()
    {
        try {
            $user = Auth::user();
            return response()->json([
                'access_token' => Auth::refresh(),
                'token_type' => 'bearer',
                'expires_in' => env('JWT_TTL') * 60,
                'user' => $user,
            ]);
        } catch (Exception $e) {
            return response()->json([
                'Message' => 'Error al refrescar el token',
                'Debug' => $e->getMessage()
            ], 500);
        }
    }
}
