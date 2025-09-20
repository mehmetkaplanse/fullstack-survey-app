<?php

namespace App\Http\Controllers;

use App\Events\UserRegistered;
use App\Http\Requests\LoginRequest;
use App\Http\Requests\RegisterRequest;
use App\Http\Resources\UserResource;
use App\Models\User;
use App\Notifications\WelcomeNotification;
use App\Traits\ApiResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\Http\Request;
use Laravel\Socialite\Facades\Socialite;

class AuthController extends Controller
{
    use ApiResponse;

    public function register(RegisterRequest $request): JsonResponse
    {
        try {
            $user = User::create($request->validated());
            $token = $user->createToken('auth-token')->plainTextToken;

            return $this->successResponse([
                'token' => $token,
                'user' => $user
            ],'User register successfully.',201);
        } catch (\Exception $e) {
            return $this->errorResponse('An error occurred, please try again.');
        }
    }

    public function login(LoginRequest $request): JsonResponse
    {
        try {
            if (!Auth::attempt($request->validated())) {
                return $this->errorResponse('Invalid credentials');
            }

            $user = User::where('email', $request->validated('email'))->first();

            if (!$user) {
                return $this->errorResponse('User not found');
            }

            $token = $user->createToken('auth-token')->plainTextToken;
            return $this->successResponse([
                'token' => $token
            ],'',200);
        } catch (\Exception $e) {
            return $this->errorResponse('An error occurred, please try again.');
        }
    }

    public function logout(): JsonResponse
    {
        try {
            $user = Auth::user();

            if (!$user) {
                return $this->errorResponse('Unauthenticated', 401);
            }

            if ($user->currentAccessToken()) {
                $user->currentAccessToken()->delete();
            }

            return $this->successResponse(null, 'Logged out successfully.');
        } catch (\Exception $e) {
            return $this->errorResponse('An error occurred while logging out.');
        }
    }

    public function redirectToGoogle()
    {
        return Socialite::driver('google')->redirect();
    }

    public function handleGoogleCallback()
    {
        try {
            $google_user = Socialite::driver('google')->user();

            $user = User::where('google_id', $google_user->getId())
                ->orWhere('email', $google_user->getEmail())
                ->first();

            if(!$user) {
                $user = User::create([
                    'name' => $google_user->getName(),
                    'email' => $google_user->getEmail(),
                    'google_id' => $google_user->getId()
                ]);
            } else {
                if(!$user->google_id) {
                    $user->google_id = $google_user->getId();
                    $user->save();
                }
            }

            $oauth_key = $google_user->token;
            $clientUrl = 'http://localhost:5175';

            return redirect($clientUrl.'/auth/callback?oauth_key='.$oauth_key);
        } catch (\Exception $e) {
            return $this->errorResponse('Google login failed.');
        }
    }

    public function loginOrRegister(Request $request): JsonResponse
    {
        $oauth_key = $request->input('oauth_key');
        $google_user = Socialite::driver('google')->stateless()->userFromToken($oauth_key);

        $user = User::where('email', $google_user->getEmail())
            ->orWhere('google_id', $google_user->getId())
            ->first();

        $avatar = $google_user->getAvatar() ?? null;

        if(!$user) {
            $user = User::create([
                'name' => $google_user->getName(),
                'email' => $google_user->getEmail(),
                'google_id' => $google_user->getId(),
                'avatar' => $avatar,
            ]);
        } else {
            if (!$user->avatar && $avatar) {
                $user->avatar = $avatar;
                $user->save();
            }
        }

        $token = $user->createToken('auth-token')->plainTextToken;

        return response()->json([
            'status' => 'success',
            'token' => $token,
            'user' => UserResource::make($user)
        ]);
    }

    public function me(): JsonResponse
    {
        $user = Auth::guard('api')->user();
        if(!$user) {
            return $this->errorResponse('Not authenticated!', 401);
        }
        return $this->successResponse($user, null, 200);
    }

}
