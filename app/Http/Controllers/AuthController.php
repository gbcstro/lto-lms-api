<?php

namespace App\Http\Controllers;

use App\Models\User;
use Hash;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Http;
use Throwable;
use Tymon\JWTAuth\Facades\JWTAuth;

class AuthController extends Controller
{
    public function register(Request $request)
    {
        try {
            if ($request->has('id_token')) {
                return $this->registerWithGoogle($request);
            }

            $this->validate($request, [
                'first_name' => 'required|string',
                'last_name' => 'required|string',
                'email' => 'required|email|unique:users',
                'username' => 'required|string|unique:users',
                'password' => 'required|string|min:6',
            ]);

            // Create user
            $user = User::create([
                'first_name' => $request->first_name,
                'last_name' => $request->last_name,
                'email' => $request->email,
                'username' => $request->username,
                'password' => $request->password,
            ]);

            // Generate JWT token
            $token = JWTAuth::fromUser($user);

            return response()->json([
                'access_token' => $token,
                'token_type' => 'bearer',
                'expires_in' => auth('api')->factory()->getTTL() * 60,
                'user' => auth()->user(),
            ]);
            
        } catch (Throwable $e) {
            report($e);
            return response()->json([
                'error' => 'Registration failed',
                'message' => $e->getMessage(),
                'code' => $e->getCode(),
            ], 500);
        }
    }

    private function registerWithGoogle(Request $request)
    {
        try {
            $this->validate($request, [
                'id_token' => 'required|string'
            ]);

            $idToken = $request->id_token;
            $googleClientId = env('GOOGLE_CLIENT_ID');

            // Verify Google ID token
            $response = Http::get("https://oauth2.googleapis.com/tokeninfo?id_token={$idToken}");

            if (!$response->successful()) {
                throw new \Exception('Invalid Google ID Token');
            }

            $payload = $response->json();

            if ($payload['aud'] !== $googleClientId) {
                throw new \Exception('Invalid Google Client ID');
            }

            // Extract name parts
            $fullName = explode(" ", $payload['name'], 2);
            $firstName = $fullName[0] ?? "";
            $lastName = $fullName[1] ?? "";

            // Generate a unique username if needed
            $username = strtolower(Str::slug($firstName . '.' . $lastName));
            $existingUser = User::where('username', $username)->exists();
            if ($existingUser) {
                $username .= rand(100, 999);
            }

            // Check if user already exists
            $user = User::where('google_id', $payload['sub'])
                        ->orWhere('email', $payload['email'])
                        ->first();

            if (!$user) {
                // Register new user with Google
                $user = User::create([
                    'first_name' => $firstName,
                    'last_name' => $lastName,
                    'email' => $payload['email'],
                    'username' => $username,
                    'google_id' => $payload['sub'],
                    'profile_picture' => $payload['picture'],
                    'password' => Hash::make(uniqid()) // Assign a random password
                ]);
            }

            // Generate JWT token
            $token = JWTAuth::fromUser($user);

            return response()->json([
                'access_token' => $token,
                'token_type' => 'bearer',
                'expires_in' => auth('api')->factory()->getTTL() * 60,
                'user' => auth()->user(),
            ]);
        } catch (Throwable $e) {
            return response()->json([
                'error' => 'Google registration failed',
                'message' => $e->getMessage(),
                'code' => $e->getCode(),
            ], 500);
        }
    }

    public function login(Request $request)
    {
        try {
            if ($request->has('id_token')) {
                return $this->loginWithGoogle($request);
            }

            $this->validate($request, [
                'username' => 'required|string',
                'password' => 'required|string',
            ]);

            $credentials = $request->only('username', 'password');

            if (!$token = JWTAuth::attempt($credentials)) {
                return response()->json(['error' => 'Invalid username or password'], 401);
            }

            return $this->respondWithToken($token);

        } catch (Throwable $e) {
            report($e);
            return response()->json([
                'error' => 'Login failed',
                'message' => $e->getMessage(),
                'code' => $e->getCode(),
            ], 500);
        }
    }

    private function loginWithGoogle(Request $request)
    {
        try {
            $this->validate($request, [
                'id_token' => 'required|string'
            ]);

            $idToken = $request->id_token;
            $googleClientId = env('GOOGLE_CLIENT_ID');

            // Verify Google ID token
            $response = Http::get("https://oauth2.googleapis.com/tokeninfo?id_token={$idToken}");

            if (!$response->successful()) {
                throw new \Exception('Invalid Google ID Token');
            }

            $payload = $response->json();

            if ($payload['aud'] !== $googleClientId) {
                throw new \Exception('Invalid Google Client ID');
            }

            // Find the user
            $user = User::where('google_id', $payload['sub'])->orWhere('email', $payload['email'])->first();

            if (!$user) {
                return response()->json(['error' => 'User not registered'], 401);
            }

            // Generate JWT token
            $token = JWTAuth::fromUser($user);

            return $this->respondWithToken($token);

        } catch (Throwable $e) {
            return response()->json([
                'error' => 'Google login failed',
                'message' => $e->getMessage(),
                'code' => $e->getCode(),
            ], 500);
        }
    }

    // Fetch current logged-in user profile
    public function me(Request $request)
    {
        try {
            // Get authenticated user
            $user = Auth::user();

            // Return the user's profile
            return response()->json([
                'user' => $user,
            ]);

        } catch (Throwable $e) {
            return response()->json([
                'error' => 'Failed to fetch user profile',
                'message' => $e->getMessage(),
                'code' => $e->getCode(),
            ], 500);
        }
    }

    private function respondWithToken($token)
    {
        return response()->json([
            'access_token' => $token,
            'token_type' => 'bearer',
            'expires_in' => auth()->factory()->getTTL() * 60,
            'user' => Auth::user(),
        ], 200);
    }
}
