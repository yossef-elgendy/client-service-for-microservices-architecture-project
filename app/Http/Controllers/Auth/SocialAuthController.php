<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Resources\Client\ClientIndexResource;
use App\Models\Client;
use Exception;
use Laravel\Socialite\Facades\Socialite;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class SocialAuthController extends Controller
{
    public function redirectToProvider($provider)
    {
        $validated = $this->validateProvider($provider);
        if (!is_null($validated)) {
            return $validated;
        }

        return Response()->json([
            'url' => Socialite::driver($provider)->stateless()->redirect()->getTargetUrl()
        ], 200);
    }

    public function handleProviderCallback($provider)
    {
        $validated = $this->validateProvider($provider);
        if (!is_null($validated)) {
            return $validated;
        }

        try {
            $client = Socialite::driver($provider)->stateless()->user();

        } catch (Exception $e) {
            return response()->json(['error' => 'Invalid credentials provided.'], 422);
        }

        $clientCreated = Client::firstOrCreate(
            [
                'email' => $client->getEmail()
            ],
            [
                'email_verified_at' => now(),
                'full_name' => $client->getName(),
                'status' => 1,
            ]
        );

        $clientCreated->providers()->updateOrCreate(
            [
                'provider' => $provider,
                'provider_id' => $client->getId(),
            ],
            [
                'avatar' => $client->getAvatar()
            ]
        );
        $token = $clientCreated->createToken($client->getEmail())->plainTextToken;

        return response()->json([
            'token' => $token,
            'client'=>new ClientIndexResource($clientCreated)
            ],
            Response::HTTP_ACCEPTED, ['Access-Token' => $token]);
    }


    protected function validateProvider($provider)
    {
        if (!in_array($provider, ['google'])) {
            return response()->json(['error' => 'Please login using google'],
             Response::HTTP_UNPROCESSABLE_ENTITY);
        }
    }

}
