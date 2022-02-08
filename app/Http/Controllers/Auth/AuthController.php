<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\LoginRequest;
use App\Http\Requests\RegisterRequest;
use App\Http\Resources\ClientResource;
use Illuminate\Http\Request;
use App\Models\Client;
use App\Models\Media;
use Exception;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Validator;
use Symfony\Component\HttpFoundation\Response;

class AuthController extends Controller
{
    public function index() {
        try{
            return response()->json([
                'client'=> ClientResource::collection(auth()->user())
           ],Response::HTTP_OK);
            // $clients = Client::paginate(2);
            // return response()->json($clients);

        } catch (Exception $e) {
            return response()->json([
                'message'=> $e->getMessage()
           ],Response::HTTP_UNAUTHORIZED);
        }


    }

    public function register(RegisterRequest $request) {
        try {
            $validator = Validator::make($request->all(), $request->rules());

            if ($validator->fails()) {
                return response()->json(['error'=>$validator->getMessageBag()], Response::HTTP_BAD_REQUEST);
            }

            $fields = $validator->validated();
            $fields['password'] = Hash::make($fields['password']);
            $fields['location'] = [
                'country' => $fields['country'],
                'city' => $fields['city'],
                'area' => $fields['area'],
            ];

            $client = Client::create(Arr::except($fields,['country','city','area','image']));

            $token = $client->createToken($fields['email'])->plainTextToken;

            if($request->image){
                $image = time().'.'.$fields['image']->extension();

                $fields['image']->move(public_path('uploads'), $image);

                $image =  Media::create([
                    "name" => $image,
                    "type"=>"Client",
                    "model_id"=> $client->id
                ]);


            }

            $response = [
                'client' => new ClientResource($client),
                'token'=> $token,
                'image'=> $request->image ? $image : ''

            ];

            return response()->json($response, Response::HTTP_CREATED);

        } catch (Exception $e){
            return response([
                'Error !!' => $e->getMessage()
            ]);
        }

    }

    public function logout() {

        try{

            auth()->user()->tokens()->delete();

            return response([
                'message'=> 'token destroyed'
            ], Response::HTTP_OK);

        }catch(Exception $e){
            return response()->json(['message' => 'Something wrong happened !', 'error'=>$e->getMessage()]);
        }

    }

    public function login(LoginRequest $request) {
        try {

            $validator = Validator::make($request->all(), $request->rules());

            if($validator->fails()){
                return response()->json($validator->getMessageBag(), Response::HTTP_UNAUTHORIZED);
            }

            $fields = $validator->validated();

            $client = Client::where('email', $fields['email'])->get();

            if($client->email) {
                if(Hash::check($fields['passowrd'], $client->password)){
                    $token = $client->createToken($fields['email'])->plainTextToken;
                    return response()->json([
                        'message' => 'Authorized',
                        'client' => new ClientResource($client),
                        'token'=> $token
                    ],Response::HTTP_ACCEPTED);

                } else {

                    return response()->json(['message' => "Invalid credintials."] , Response::HTTP_UNAUTHORIZED);

                }

            } else {

                return response()->json(['message' => "This e-mail doesn't exist."] , Response::HTTP_UNAUTHORIZED);

            }
        } catch (Exception $e) {

            return response()->json(['message' => $e->getMessage()] );

        }

    }
}
