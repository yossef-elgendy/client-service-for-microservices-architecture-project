<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\Reservation\ReservationIndexResource;
use App\Jobs\Sent\ReservationCreated;
use App\Models\Reservation;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Symfony\Component\HttpFoundation\Response;

class ReservationController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        try {

            $validator = Validator::make($request->all(),[
                'nursery_id'=>'required|integer',
                'child_id'=>'required|integer',
                'courses' => 'array',
                'activities' => 'array'
            ]);

            if ($validator->fails()) {
                return response()->json(['error'=>$validator->getMessageBag()], Response::HTTP_BAD_REQUEST);
            }

            $fields = $validator->validated();
            $fields['client_id'] = auth()->user()->id;
            $reservation = Reservation::create($fields);

            ReservationCreated::dispatch($reservation)->onQueue('provider')->onConnection('rabbitmq');

            return response()->json([
                'reservation' => new ReservationIndexResource($reservation)
            ], Response::HTTP_CREATED);



        } catch (Exception $e) {
            return response()->json([
                'Error !!' => $e->getMessage(),
                'Line'=> $e->getLine()
            ], Response::HTTP_NOT_FOUND);
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
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        //
    }
}
