<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreClientRequest;
use App\Http\Requests\UpdateClientRequest;
use App\Http\Resources\Client\ClientShowResource;
use App\Models\Client;
use Illuminate\Support\Facades\Validator;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Hash;
use Symfony\Component\HttpFoundation\Response;

class ClientController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        try{
            return response()->json([
                'client'=> new ClientShowResource($request->user())
           ],Response::HTTP_OK);

        } catch (Exception $e) {
            return response()->json([
                'message'=> $e->getMessage()
           ],Response::HTTP_UNAUTHORIZED);
        }
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(StoreClientRequest $request)
    {
        try {
                $validator = Validator::make($request->all(), $request->rules());

                if ($validator->fails()) {
                    return response()->json(['error'=>$validator->getMessageBag()]
                    , Response::HTTP_BAD_REQUEST);
                }

                $fields = $validator->validated();
                $fields['password'] = Hash::make($fields['password']);
                $fields['location'] = [
                    'country' => $request->country,
                    'city' => $request->city,
                    'area' => $request->area,
                ];

                $client = Client::create(Arr::except($fields,['country','city','area','image']));

                $token = $client->createToken($fields['email'])->plainTextToken;
                // event(new Registered($client));



            $response = [
                'client' => new ClientShowResource($client),
                'token' => $token,
            ];

            return response()->json($response, Response::HTTP_CREATED);

        } catch (Exception $e){
            return response([
                'Error !!' => $e->getMessage()
            ]);
        }
    }



    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(UpdateClientRequest $request, $id)
    {
        try {
                if( !$client = Client::find($id) ){
                    return response()->json(['error' => 'You can not update this client.'], 401);
                }

                if($client->id !== $request->user()->id) {
                    return response()->json(['error' => 'You can not update this client.'], 401);
                }

                $validator = Validator::make($request->all(), $request->rules());
                if($validator->fails()) {
                    return response()->json(['error' => $validator->getMessageBag()],
                    Response::HTTP_NOT_ACCEPTABLE);
                }

                $data = $validator->validated();

                if($request->password) {
                    $data['password'] = Hash::make($data['password']);
                }

                $client->update(Arr::except($data, ['mediafile']));




                return response()->json(new ClientShowResource($client), Response::HTTP_CREATED);

        } catch(Exception $e) {
                return response()->json(['error' => $e->getMessage()], Response::HTTP_NOT_FOUND);
        }

    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy(Request $request, $id)
    {
        try {
			if(! $client = Client::find($id)) {
				return response()->json(['error' => 'You can not delete this client.'], 401);
			}

			if($client->id !== $request->user()->id) {
				return response()->json(['error' => 'You can not delete this client.'], 401);
			}

            $children_count = $client->loadCount('children');

            if($children_count) {
                if($children_count > 1) return response()->json(
                    ['error' => "Client has $children_count children. Cannot delete."],
                    401
                );

                if( $children_count = 1) return response()->json(
                    ['error' => "Client has $children_count child. Cannot delete."],
                    401
                );

            }


            $mediafile = new MediafileController();
            $mediafile->destroy_all($client->id, 'client');

            $client->delete();

			return response()->json(['message' => 'Client deleted'], Response::HTTP_OK);
		} catch (Exception $e) {
			return response()->json(['error' => $e->getMessage()], Response::HTTP_NOT_FOUND);
		}
    }

}
