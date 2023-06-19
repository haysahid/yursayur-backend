<?php

namespace App\Http\Controllers\API;

use Exception;
use App\Models\User;
use Illuminate\Http\Request;
use App\Helpers\ResponseFormatter;
use Laravel\Fortify\Rules\Password;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;

class UserController extends Controller
{
    public function register(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users',
            'password' => ['required', 'string', new Password],
        ]);

        try {
            User::create([
                'name' => $request->input('name'),
                'email' => $request->input('email'),
                'password' => Hash::make($request->password),
                'role' => 'enduser',
            ]);

            $user = User::where('email', $request->input('email'))->first();
            $token = $user->createToken('authToken')->plainTextToken;

            return ResponseFormatter::success([
                'access_token' => $token,
                'token_type' => 'Bearer',
                'user' => $user,
            ], 'Akun berhasil dibuat', 201);
        } catch (Exception $error) {
            return ResponseFormatter::error('Ada yang salah. Autentikasi gagal.', 500);
        }
    }

    public function login(Request $request)
    {
        $request->validate([
            'email' => 'required|string|email',
            'password' => 'required',
        ]);

        try {
            $credentials = request(['email', 'password']);

            if (!Auth::attempt($credentials)) {
                return ResponseFormatter::error('Email atau password salah. Autentikasi gagal.', 401);
            }

            $user = User::where('email', $request->input('email'))->first();

            if (!Hash::check($request->input('password'), $user->password, [])) {
                throw new Exception('Invalid credentials');
            }

            if ($user->disabled_at) {
                return ResponseFormatter::error('Status pengguna tidak aktif. Hubungi Admin apabila ada kesalahan.', 400);
            }

            $token = $user->createToken('authToken')->plainTextToken;

            return ResponseFormatter::success([
                'access_token' => $token,
                'token_type' => 'Bearer',
                'user' => $user,
            ], 'Berhasil masuk', 200);
        } catch (Exception $error) {
            return ResponseFormatter::error('Ada yang Salah. Autentikasi gagal.'  . $error, 500);
        }
    }

    public function fetch()
    {
        $user = User::find(Auth::user()->id);

        return ResponseFormatter::success([
            'user' => $user,
        ], 'Data pengguna ditemukan', 200);
    }

    public function updateProfile(Request $request)
    {
        $request->validate([
            'name' => 'nullable|string|max:255',
            'address' => 'nullable|string|max:255',
            'phone' => 'nullable|string|max:255',
            'avatar' => 'nullable|file',

        ]);

        $user = user::query()->find(Auth::user()->id);

        if ($user->email != $request->input('email')) {
            $request->validate([
                'email' => 'required|string|email|max:255|unique:users',
            ]);
        }

        try {
            $user->update([
                'name' => $request->input('name'),
                'email' => $request->input('email'),
                'address' => $request->input('address'),
                'phone' => $request->input('phone'),
            ]);

            if ($request->hasFile('avatar')) {
                $avatar_path = '';

                // Delete old avatar
                if ($user->avatar) {
                    Storage::delete($user->avatar);
                }

                // Store avatar 
                $avatar_path = $request->file('avatar')->store('user');

                // Add to database
                $user->update([
                    'avatar' => $avatar_path,
                ]);
            }

            return ResponseFormatter::success([
                'user' => $user,
            ], 'Data pengguna berhasil diubah', 200);
        } catch (Exception $error) {
            return ResponseFormatter::error('Ada yang salah. Autentikasi gagal.', 500);
        }
    }

    public function logout(Request $request)
    {
        $token = $request->user()->currentAccessToken()->delete();

        return ResponseFormatter::success([
            'token' => $token,
        ], 'Berhasil keluar', 200);
    }
}
