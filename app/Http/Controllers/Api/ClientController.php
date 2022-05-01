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
            $client = Client::find($request->client_id);

            if($client){
                return response()->json([
                    'client'=> new ClientShowResource($client),
                    'status'=>Response::HTTP_OK
               ]);
            } else {
                return response()->json([
                    'client'=> null,
                    'status'=>Response::HTTP_NOT_FOUND
               ]);
            }


        } catch (Exception $e) {
            return response()->json([
                'error' => $e->getMessage(),
                'status'=>Response::HTTP_NOT_FOUND
            ]);
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

                    return response()->json([
                        'error' => $validator->getMessageBag(),
                        'status'=>Response::HTTP_BAD_REQUEST
                    ]);
                }

                $fields = $validator->validated();
                $fields['location'] = [
                    'country' => $request->country,
                    'city' => $request->city,
                    'area' => $request->area,
                ];

                $client = Client::create(Arr::except($fields,['country','city','area','image']));





            $response = [
                'client' => new ClientShowResource($client),
                'status' => Response::HTTP_CREATED
            ];

            return response()->json($response);

        } catch (Exception $e){
            return response()->json([
                'error' => $e->getMessage(),
                'status'=>Response::HTTP_NOT_FOUND
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

                $client = Client::find($id);
                $validator = Validator::make($request->all(), $request->rules());
                if($validator->fails()) {
                    return response()->json([
                        'error' => $validator->getMessageBag(),
                        'status' =>Response::HTTP_NOT_ACCEPTABLE
                    ]);
                }

                $data = $validator->validated();


                if($request->gender && $client->gender != null){
                    $data =  Arr::except($data, ['gender']);
                }

                $client->update($data);




                return response()->json([
                    'client' => new ClientShowResource($client),
                    'status' => Response::HTTP_CREATED
                ]);

        } catch(Exception $e) {
            return response()->json([
                'error' => $e->getMessage(),
                'status'=>Response::HTTP_NOT_FOUND
            ]);
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
            $client = Client::find($id);


            $children_count = $client->loadCount('children');

            if($children_count) {
                if($children_count > 1) return response()->json([
                        'error' => "Client has $children_count children. Cannot delete.",
                        'status' => 401
                    ]);

                if( $children_count = 1) return response()->json([
                        'error' => "Client has $children_count child. Cannot delete.",
                        'status'=> 401
                    ]);

            }


            $client->delete();

			return response()->json([
                'message' => 'Client deleted',
                'status' =>Response::HTTP_OK
            ]);
		} catch (Exception $e) {
			return response()->json([
                'error' => $e->getMessage(),
                'status'=>Response::HTTP_NOT_FOUND
            ]);
		}
    }

}
