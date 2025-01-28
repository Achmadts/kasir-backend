<?php

namespace App\Http\Controllers;

use Exception;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Http;
use App\Http\Controllers\Controller;
use Laravel\Socialite\Facades\Socialite;
use Illuminate\Http\Request;
use App\Http\Requests\SocialLoginRequest;

class SocialLoginController extends Controller
{
    public function login(SocialLoginRequest $request): JsonResponse
    {
        try {
            $accessToken = $request->get('access_token');
            $provider = $request->get('provider');
            $providerUser = Socialite::driver($provider)->userFromToken($accessToken);

            if (!$providerUser || !$providerUser->getEmail()) {
                return response()->json(['message' => 'Unable to fetch user information'], 400);
            }

            $user = $this->findOrCreate($providerUser, $provider);
            $token = auth('api')->login($user);

            return response()->json([
                'message' => 'Logged in successfully',
                'data' => [
                    'user' => $user,
                    'token' => $token,
                ],
            ]);
        } catch (\Laravel\Socialite\Two\InvalidStateException $e) {
            return response()->json(['message' => 'Invalid state'], 400);
        } catch (Exception $exception) {
            return response()->json(['message' => $exception->getMessage()], 500);
        }
    }

    protected function findOrCreate(ProviderUser $providerUser, string $provider): User
    {
        $linkedSocialAccount = LinkedSocialAccount::where('provider_name', $provider)
            ->where('provider_id', $providerUser->getId())
            ->first();

        if ($linkedSocialAccount) {
            return $linkedSocialAccount->user;
        }

        $user = User::firstOrCreate(
            ['email' => $providerUser->getEmail()],
            [
                'name' => $providerUser->getName(),
                'email_verified_at' => now(),
                'images' => $providerUser->getAvatar(),
            ]
        );

        if (!$user->images) {
            $user->update(['images' => $providerUser->getAvatar()]);
        }

        $user->linkedSocialAccounts()->create([
            'provider_id' => $providerUser->getId(),
            'provider_name' => $provider,
        ]);

        return $user;
    }

    public function handleGoogleCallback(Request $request)
    {
        try {
            $googleUser = Socialite::driver('google')->stateless()->user();
            $user = User::firstOrCreate(
                ['email' => $googleUser->getEmail()],
                [
                    'name' => $googleUser->getName(),
                    'images' => $googleUser->getAvatar(),
                    'email_verified_at' => now(),
                ]
            );

            $token = auth('api')->login($user);
            return redirect("http://localhost:5173?token=$token&is_admin={$user->is_admin}");
            // return response()->json([
            //     'message' => 'Logged in successfully',
            //     'data' => [
            //         'user' => $user,
            //         'token' => $token,
            //     ],
            // ]);
        } catch (\Exception $e) {
            return response()->json(['message' => $e->getMessage()], 500);
        }
    }
}
