<?php

namespace App\Http\Controllers;

use App\Http\Requests\LoginRequest;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log; 
use Illuminate\Validation\ValidationException;
use Laravel\Sanctum\PersonalAccessToken;

class AuthController extends Controller
{
    public function login(LoginRequest $request): JsonResponse
    {
        $credentials = $request->validated();

        $user = User::where('email', $credentials['email'])->first();

        if (! $user || ! Hash::check($credentials['password'], $user->password)) {
            Log::warning('Autentikasi gagal: kredensial tidak valid', [
                'ip_address' => $request->ip(),
                'email' => $credentials['email'],
                'action' => 'user_login',
                'reason' => 'Email tidak terdaftar atau password salah'
            ]);

            throw ValidationException::withMessages([
                'email' => ['Email atau password salah.'],
            ]);
        }

        $deviceName = $credentials['device_name'] ?? 'api-client';
        $token = $user->createToken($deviceName)->plainTextToken;

        Log::info('Pengguna berhasil melakukan login', [
            'user_id' => $user->id,
            'action' => 'user_login',
            'reason' => 'Kredensial valid dan token akses berhasil diterbitkan'
        ]);

        return response()->json([
            'message' => 'Login berhasil.',
            'data' => [
                'user' => $user,
                'token' => $token,
                'token_type' => 'Bearer',
            ],
        ]);
    }

    public function logout(Request $request): JsonResponse
    {
        $currentToken = $request->user()->currentAccessToken();

        if ($currentToken instanceof PersonalAccessToken) {
            $currentToken->delete();
        }

        Log::info('Pengguna berhasil melakukan logout', [
            'user_id' => $request->user()->id,
            'action' => 'user_logout',
            'reason' => 'Token akses saat ini berhasil dicabut dari sistem'
        ]);

        return response()->json([
            'message' => 'Logout berhasil. Token telah dicabut.',
        ]);
    }

    public function me(Request $request): JsonResponse
    {
        return response()->json([
            'message' => 'Data user berhasil diambil.',
            'data' => $request->user(),
        ]);
    }
}
