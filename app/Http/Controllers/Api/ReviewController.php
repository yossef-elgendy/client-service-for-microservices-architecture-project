<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreReviewRequest;
use App\Http\Requests\UpdateReviewRequest;
use App\Http\Resources\Review\ReviewResource;
use App\Models\Review;
use Exception;
use Illuminate\Support\Facades\Validator;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

use function PHPSTORM_META\type;

class ReviewController extends Controller
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

            if($request->model_type == "course"){
                $reviews  = Review::where([
                    ['model_type', '=', 'App\CourseNursery'],
                    ['model_id', '=', $request->model_id]
                    ])->get();

                    return response()->json([
                        'reviews' => ReviewResource::collection($reviews)
                    ], Response::HTTP_ACCEPTED);
            }

            if ($request->model_type == "nursery") {
                $reviews  = Review::where([
                    ['model_type', '=', 'App\Nursery'],
                    ['model_id', '=', $request->model_id]
                    ])->get();

                    return response()->json([
                        'reviews' => ReviewResource::collection($reviews),
                    ], Response::HTTP_ACCEPTED);
            }

            return response()->json([
                'reviews' => []
            ], Response::HTTP_ACCEPTED);


        } catch (Exception $e) {
            return response()->json(['error' => $e->getMessage()], Response::HTTP_NOT_FOUND);

        }

    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(StoreReviewRequest $request)
    {
        try{

            $validator = Validator::make($request->all(),$request->rules());

            if ($validator->fails()) {
                return response()->json(['error'=>$validator->getMessageBag()],
                    Response::HTTP_BAD_REQUEST);
            }

            $fields = $validator->validated();

            if($request->model_type == "nursery"){
                $fields['model_type'] = 'App\Nursery';
            } elseif($request->model_type == "course") {
                $fields['model_type'] = 'App\CourseNursery';
            }

            $review = Review::create($fields);

            return response()->json([
                'review' => new ReviewResource($review)
            ], Response::HTTP_CREATED);

        }catch( Exception $e) {
            return response()->json(['error' => $e->getMessage()], Response::HTTP_NOT_FOUND);
        }


    }



    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(UpdateReviewRequest $request, $id)
    {
        try{

            $review = Review::find($id);
            if($review->client_id !=  $request->client_id){
                return response()->json([
                    'error' => 'You cannot update this review.'
                ], Response::HTTP_NOT_ACCEPTABLE);
            }



            $validator = Validator::make($request->all(),$request->rules());
            if ($validator->fails()) {
                return response()->json([
                    'error'=>$validator->getMessageBag()
                ], Response::HTTP_BAD_REQUEST);
            }

            $fields = $validator->validated();

            $review = $review->update([
                'content' => $fields['content']?? $review->content,
                'rate'=> $fields['rate']?? $review->rate,
            ]);

            return response()->json([
                'review' => new ReviewResource($review)
            ], Response::HTTP_ACCEPTED);

        }catch(Exception $e){
            return response()->json(['error' => $e->getMessage()], Response::HTTP_NOT_FOUND);
        }
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @param  \Illuminate\Http\Request $request
     * @return \Illuminate\Http\Response
     */
    public function destroy($id, Request $request)
    {
        try{

            $review = Review::findOrFail($id);
            if($review->client_id !=  $request->client_id){
                return response()->json(['error' => 'You cannot delete this review'], Response::HTTP_NOT_ACCEPTABLE);
            }

            $review->delete();
            return response()->json([
                'message' => 'Review has been deleted successfully.'
            ], Response::HTTP_ACCEPTED);

        } catch(Exception $e) {
            return response()->json(['error' => $e->getMessage()], Response::HTTP_NOT_FOUND);
        }
    }
}
