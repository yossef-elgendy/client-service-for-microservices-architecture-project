<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreChildRequest;
use App\Http\Requests\UpdateChildRequest;
use App\Http\Resources\ChildResource;
use App\Models\Child;
use Exception;
use Symfony\Component\HttpFoundation\Response;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

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
                'children' => ChildResource::collection($children)
            ],Response::HTTP_ACCEPTED);

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
            $fields['client_id'] = auth()->user()->id;
            $child = Child::create($fields);

            return response()->json([
                'child' => new ChildResource($child)
            ], Response::HTTP_CREATED);


        } catch (Exception $e) {
            return response()->json([
                'Error !!' => $e->getMessage()
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
                'child' => new ChildResource($child)
            ],Response::HTTP_ACCEPTED);

        } catch (Exception $e) {
            return response()->json([
                'Error !!' => $e->getMessage()
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

            $child->full_name = $request->full_name ? $fields['full_name']: $child->full_name;
            $child->age = $request->age ? $fields['age']: $child->age;
            $child->nursery_id = $request->nursery_id ? $fields['nursery_id']: $child->nursery_id;
            $child->gender = $request->gender ? $fields['gender']: $child->gender;

            $child->save();

            return response()->json([
                'child' => new ChildResource($child),
            ], Response::HTTP_ACCEPTED);

        } catch (Exception $e) {

            return response()->json([
                'Error !!' => $e->getMessage()
            ], Response::HTTP_NOT_FOUND);

        }
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        try {
            $child = Child::where([
                ['client_id', '=',auth()->user()->id],
                ['id','=',$id],
                ])
            ->firstOrFail();

            $child->delete();

            return response()->json(['message' => 'Child info has been deleted'], Response::HTTP_ACCEPTED);

        } catch (Exception $e) {
            return response()->json([
                'Error !!' => $e->getMessage()
            ], Response::HTTP_NOT_FOUND);
        }

    }
}
