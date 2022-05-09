<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreReservationRequest;
use App\Http\Requests\UpdateReservationRequest;
use App\Http\Resources\Reservation\ReservationCreatedJobResource;
use App\Http\Resources\Reservation\ReservationIndexResource;
use App\Jobs\ClientDispatched\ClientReservationReject;
use App\Jobs\ClientDispatched\ReservationCreated;
use App\Models\Reservation;
use App\Models\Child;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Validator;
use Symfony\Component\HttpFoundation\Response;

class ReservationController extends Controller
{


    /**
     * Display a listing of the resource.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        try{

            $reservations = Reservation::where(
                [['client_id', '=', $request->client_id]]
                )->get();

            return response()->json([
                'reservations' => $reservations,
                'status' =>Response::HTTP_ACCEPTED
            ]);
        } catch (Exception $e){
            return response()->json([
                'Error !!' => $e->getMessage(),
                'Line'=> $e->getLine(),
                'status' =>Response::HTTP_NOT_FOUND
            ]);
        }
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */

    public function store(StoreReservationRequest $request)
    {
        try {

            $validator = Validator::make($request->all(), $request->rules());

            if ($validator->fails()) {
                return response()->json([
                    'error'=>$validator->getMessageBag(),
                    'status'=> 400
                ]);
            }


            if($request->client_id != Child::find($request->child_id)->client_id){
                return response()->json([
                    'error'=>'Error the child don\'t exist.',
                    'status'=> 400
                ]);
            }


            $fields = $validator->validated();
            $fields['client_id'] = $request->client_id;


            $reservation = Reservation::create($fields);


            ReservationCreated::dispatch(new ReservationCreatedJobResource($reservation))
            ->onQueue('provider')
            ->onConnection('rabbitmq');

            return response()->json([
                'message'=> 'Your reservation request will be sent to the nursery.',
                'reservation' => new ReservationIndexResource($reservation),
                'status'=> 201
            ]);



        } catch (Exception $e) {
            return response()->json([
                'error' => $e->getMessage(),
                'line'=> $e->getLine(),
                'status' => 404
            ]);
        }

    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */

    public function show(Request $request, $id)
    {
        try {
            $reservation = Reservation::find($id);
			if(! $reservation || $reservation->client_id != $request->client_id ) {
				return response()->json([
                    'error' => 'You can not show this reservation.',
                    'status' => Response::HTTP_UNAUTHORIZED
                ]);
			}

			return response()->json([
                'reservation'=> new ReservationIndexResource($reservation),
                'status'=> Response::HTTP_ACCEPTED
            ]);
		} catch (\Exception $e) {
			return response()->json([
                'error' => $e->getMessage(),
                'status'=> Response::HTTP_NOT_FOUND
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

    public function update(UpdateReservationRequest $request, $id)
    {
        try {
            $reservation = Reservation::find($id);
			if(! $reservation || $reservation->client_id != $request->client_id ) {
				return response()->json([
                    'error' => 'You can not update this reservation.',
                    'status'=> Response::HTTP_UNAUTHORIZED
                ]);
			}

            if($reservation->provider_end) {
				return response()->json([
                    'error' => 'Nursery canceled this reservation.',
                    'status' => 401
                ]);
			}

            $validator = Validator::make($request->all(), $request->rules());

			if($validator->fails()) {
				return response()->json([
                    'error' => $validator->getMessageBag(),
                    'status' =>Response::HTTP_NOT_ACCEPTABLE
                ]);
			}

            $data = $validator->validated();

            if($reservation->status == 1){
                $reservation->delete();
                return response()->json([
                    'error' => 'Nursery canceled this reservation already.',
                    'status'=> Response::HTTP_ALREADY_REPORTED
                ]);
            } else {

                if($request->client_end){
                    $reservation->update(['client_end'=> $data['client_end']]);

                    ClientReservationReject::dispatch($reservation->id)
                    ->onQueue('provider')
                    ->onConnection('rabbitmq');

                    $reservation->delete();

                    return response()->json([
                            'message' => 'Your reply will be sent to client',
                            'status' => Response::HTTP_ACCEPTED
                        ]);

                }

            }


            $reservation->update(['client_end'=> $data['client_end'] ?? 0 ]);
            return response()->json([
                'message' => 'No Changes',
                'status'=>Response::HTTP_CONTINUE
            ]);

		} catch (Exception $e) {
			return response()->json([
                'error' => $e->getMessage(),
                'status' => Response::HTTP_NOT_FOUND
            ]);
		}
    }






}
