<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;

use Illuminate\Http\Request;
use App\Models\Client;
use Exception;
use Illuminate\Auth\Events\Registered;
use Illuminate\Support\Facades\Cookie;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Symfony\Component\HttpFoundation\Response;

class AuthController extends Controller
{

    public function logout(Request $request) {

        try {

            $request->user()->currentAccessToken()->delete();
            return response([
                'message'=> 'token destroyed'
            ], Response::HTTP_OK);

        } catch(Exception $e) {
            return response()->json(['message' => 'Something wrong happened !', 'error'=>$e->getMessage()], Response::HTTP_NOT_FOUND);
        }

    }

    public function login(Request $request) {
        try {

            $validator = Validator::make($request->all(), [
                "email" => "required|string|email",
                "password"=> "required|string"
            ]);

            if($validator->fails()){
                return response()->json($validator->getMessageBag(), Response::HTTP_NOT_ACCEPTABLE);
            }

            $fields = $validator->validated();

            $client = Client::where('email', $request->email)->first();

			if(!$client || !Hash::check($fields['password'], $client->password)) {
				return response()->json(['error' => 'Your cridentials are not correct'], Response::HTTP_NON_AUTHORITATIVE_INFORMATION);
			}
            $token = $client->createToken($request->email)->plainTextToken;

            return response()->json(['token' => $token], Response::HTTP_OK);

        } catch (Exception $e) {

            return response()->json(['message' => $e->getMessage()], Response::HTTP_NOT_FOUND );

        }

    }

}
