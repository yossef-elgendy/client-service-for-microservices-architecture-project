<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreChildRequest;
use App\Http\Requests\UpdateChildRequest;
use App\Http\Resources\Child\ChildIndexResource;
use App\Models\Child;
use App\Models\Media;
use Exception;
use Symfony\Component\HttpFoundation\Response;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Validator;
use Symfony\Component\HttpFoundation\Test\Constraint\ResponseIsSuccessful;

class ChildController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        try {

            $children = Child::where('client_id', auth()->user()->id)->get();

            return response()->json([
                'children' => ChildIndexResource::collection($children)
            ], Response::HTTP_ACCEPTED);

        } catch(Exception $e) {

            return response()->json([
                'Error !!' => $e->getMessage()
            ], Response::HTTP_NOT_FOUND);

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
                return response()->json(['error'=>$validator->getMessageBag()], Response::HTTP_BAD_REQUEST);
            }

            $fields = $validator->validated();
            $fields['client_id'] = $request->user()->id;
            $child = Child::create(Arr::except($fields, ['mediafile']));

            if($request->mediafile) {
                $mediafile_data = [
                  'mediafile' => $request->mediafile,
                  'mediafile_type' => $request->mediafile_type,
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
                    'error' => $mediafile_store_response,
                    'data' => new ChildIndexResource($child)
                ],
                Response::HTTP_CREATED);
            }

            return response()->json([
                'child' => new ChildIndexResource($child)
            ], Response::HTTP_CREATED);


        } catch (Exception $e) {
            return response()->json([
                'error' => $e->getMessage()
            ], Response::HTTP_BAD_REQUEST);
        }


    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        try {

            $child = Child::findOrFail($id);
            return response()->json([
                'child' => new ChildIndexResource($child)
            ],Response::HTTP_ACCEPTED);

        } catch (Exception $e) {
            return response()->json([
                'error' => $e->getMessage()
            ], Response::HTTP_NOT_FOUND);
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

            $child = Child::findOrFail($id);

            $validator = Validator::make($request->all(), $request->rules());

            if($validator->fails()){
                return response()->json(['error'=>$validator->getMessageBag()], Response::HTTP_BAD_REQUEST);
            }

            $fields = $validator->validated();

            $child->update(Arr::except($fields, ['mediafile']));

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
                        'error' => $mediafile_update_response,
                        'data' => new ChildIndexResource($child)
                    ],
                    Response::HTTP_CREATED);
                }
            }

            return response()->json([
                'child' => new ChildIndexResource($child),
            ], Response::HTTP_ACCEPTED);

        } catch (Exception $e) {

            return response()->json([
                'error' => $e->getMessage()
            ], Response::HTTP_NOT_FOUND);

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
            $child = Child::where([
                ['client_id', '=', $request->user()->id],
                ['id','=',$id],
                ])
            ->firstOrFail();

            $child->delete();

            return response()->json(['message' => 'Child info has been deleted'],
             Response::HTTP_ACCEPTED);

        } catch (Exception $e) {
            return response()->json([
                'error' => $e->getMessage()
            ], Response::HTTP_NOT_FOUND);
        }

    }
}
