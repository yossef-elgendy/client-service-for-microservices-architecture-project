<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreChildRequest;
use App\Http\Requests\UpdateChildRequest;
use App\Http\Resources\Child\ChildIndexResource;
use App\Jobs\ClientDispatched\ClientChildUpdateJob;
use App\Models\Child;
use App\Models\Media;
use Exception;
use Symfony\Component\HttpFoundation\Response;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Validator;

class ChildController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        try {
            if( $request->isAdmin && !$request->client_id ){
                $children = Child::all();
            }

            if($request->client_id){
                $children = Child::where('client_id', $request->client_id)->get();
            }


            return response()->json([
                'children' => ChildIndexResource::collection($children),
                'status' =>Response::HTTP_ACCEPTED
            ]);

        } catch(Exception $e) {

            return response()->json([
                'errors' =>[ $e->getMessage()],
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
    public function store(StoreChildRequest $request)
    {
        try {
            $validator = Validator::make($request->all(), $request->rules());

            if ($validator->fails()) {
                return response()->json([
                    'errors'=>$validator->getMessageBag(),
                    'status'=>Response::HTTP_BAD_REQUEST
                ]);
            }

            $fields = $validator->validated();
            $child = Child::create(Arr::except($fields, ['mediafile']));

            if($request->mediafile) {
                $mediafile_data = [
                  'mediafile' => $request->mediafile,
                  'mediafile_type' => Media::TYPE['profile_image'],
                  'model_id' => $child->id,
                  'model_type' => 'App\Child',
                  'is_default' => false
                ];
            } else {
                    $mediafile_data = [
                    'mediafile' => null,
                    'mediafile_type' => 'child_image',
                    'model_id' => $child->id,
                    'model_type' => 'App\Child',
                    'is_default' => true
                    ];
            }

            $mediafile = new MediafileController();
            $mediafile_store_response = $mediafile->store($mediafile_data);

            if($mediafile_store_response !== 'success') {
                return response()->json(
                [
                    'message' => 'Mediafile not uploaded! Default file is used instead',
                    'errors' => [$mediafile_store_response],
                    'data' => new ChildIndexResource($child),
                    'status'=>Response::HTTP_CREATED
                ]);
            }

            return response()->json([
                'child' => new ChildIndexResource($child),
                'status'=> Response::HTTP_CREATED
            ]);


        } catch (Exception $e) {
            return response()->json([
                'errors' => [$e->getMessage()],
                'status'=>Response::HTTP_NOT_FOUND
            ]);

        }


    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show(Request $request, $id)
    {
        try {

            if(!$child = Child::find($id)){
                return response()->json([
                    'errors' => ['Child not found.'],
                    'status'=> Response::HTTP_NOT_FOUND
                ]);
            }

            if($request->client_id != $child->client_id && !$request->isAdmin){
                return response()->json([
                    'errors' => ['You can not show this child.'],
                    'status'=> 403
                ]);
            }

            return response()->json([
                'child' => new ChildIndexResource($child)
            ],Response::HTTP_ACCEPTED);

        } catch (Exception $e) {
            return response()->json([
                'errors' => [$e->getMessage()],
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
    public function update(UpdateChildRequest $request, $id)
    {
        try {

            if(!$child = Child::find($id)){
                return response()->json([
                    'errors' => ['Child not found.'],
                    'status'=> Response::HTTP_NOT_FOUND
                ]);
            }

            if($request->client_id != $child->client_id){
                return response()->json([
                    'errors' => ['You can not update this child.'],
                    'status'=> 401
            ]);
            }

            $validator = Validator::make($request->all(), $request->rules());

            if($validator->fails()){
                return response()->json([
                    'child' => new ChildIndexResource($child),
                    'errors'=>$validator->getMessageBag(),
                    'status'=>Response::HTTP_BAD_REQUEST
                ]);
            }

            $fields = $validator->validated();

            $child->update(Arr::except($fields, ['mediafile']));

            ClientChildUpdateJob::dispatch([
                'child_id' => $id,
                'name' => $child->name,
                'age'=> $child->age,
            ])
            ->onConnection('rabbitmq')
            ->onQueue(config('queue.rabbitmq_queue.provider_service'));

            if($request->mediafile){

                $mediafile_data = [
                    'mediafile' => $request->mediafile,
                    'mediafile_type' => 'child_image',
                    'model_id' => $child->id,
                    'model_type' => 'App\Child',
                    'is_default' => false
                ];

                $mediafile = new MediaFileController();
                $id = Media::where([
                    ['model_type', '=', 'App\Child'],
                    ['model_id', '=', $child->id]
                    ])->get('id');

                $mediafile_update_response = $mediafile->update($mediafile_data, $id);

                if($mediafile_update_response !== 'success') {
                    return response()->json(
                    [
                        'message' => 'Mediafile not uploaded! Default file is used instead',
                        'errors' => [$mediafile_update_response],
                        'data' => new ChildIndexResource($child),
                        'status'=> Response::HTTP_CREATED
                    ]);
                }
            }

            return response()->json([
                'child' => new ChildIndexResource($child),
                'status'=> Response::HTTP_ACCEPTED
            ]);

        } catch (Exception $e) {

            return response()->json([
                'errors' => [$e->getMessage()],
                'status' =>Response::HTTP_NOT_FOUND
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

            if(!$child = Child::find($id)){
                return response()->json([
                    'errors' => ['Child not found.'],
                    'status'=> Response::HTTP_NOT_FOUND
                ]);
            }

            if($request->client_id != $child->client_id && !$request->isAdmin){
                return response()->json([
                    'errors' =>['You can not delete this child.'],
                    'status'=>401
            ]);
            }


            $child->delete();

            return response()->json([
                'messages' => ['Child info has been deleted'],
                'status'=>Response::HTTP_ACCEPTED
            ]);

        } catch (Exception $e) {
            return response()->json([
                'errors' => [$e->getMessage()],
                'status'=>Response::HTTP_NOT_FOUND
            ]);
        }

    }
}
