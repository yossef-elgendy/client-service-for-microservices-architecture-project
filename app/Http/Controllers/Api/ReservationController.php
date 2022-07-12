<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreReservationRequest;
use App\Http\Requests\UpdateReservationRequest;
use App\Http\Resources\Reservation\ReservationIndexResource;
use App\Jobs\ClientDispatched\ClientReservationCancelJob;
use App\Jobs\ClientDispatched\ClientReservationCreateJob;
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
            if($request->isAdmin && !$request->client_id){
                $reservations = Reservation::all();
            } else {
                $reservations = Reservation::where(
                    [['client_id', '=', $request->client_id]]
                    )->get();
            }
            

            return response()->json([
                'reservations' => ReservationIndexResource::collection($reservations),
                'status' => Response::HTTP_ACCEPTED
            ]);

        } catch (Exception $e){
            return response()->json([
                'errors' => [$e->getMessage()],
                'line'=> $e->getLine(),
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
                    'errors'=> Arr::flatten($validator->getMessageBag()),
                    'status'=> 400
                ]);
            }

            
            if($request->client_id != Child::find($request->child_id)->client_id){
                return response()->json([
                    'errors'=>['Error the child don\'t exist.'],
                    'status'=> 400
                ]);
            }

            $reservation_waiting = Reservation::where('child_id', '=',$request->child_id)
            ->whereIn('status', array(1, 0))->first();

            $reservation_active =  Reservation::where( 'child_id',$request->child_id)
            ->whereIn('status', array(2, 5))->first();

            if($reservation_waiting){
                    return response()->json([
                        'errors'=>['Error the child is already waiting for nursery response.'],
                        'status'=> 401
                    ]);
            }

            if($reservation_active){
                return response()->json([
                    'errors'=>['Error the child already has an active reservation.'],
                    'status'=> 401
                ]);
            }
            


            $fields = $validator->validated();
            $fields['client_id'] = $request->client_id;
            $fields['reservation_start_date'] = $fields['reservation_start_date']?? date("Y-m-d");

            $reservation = Reservation::create($fields);
            $child = Child::findOrFail($reservation->child_id);

            ClientReservationCreateJob::dispatch([
                'reservation_id' => $reservation->id,
                'client_id'=> $reservation->client_id,
                'nursery_id'=> $reservation->nursery_id,
                'reservation_type' => $reservation->type,
                'reservation_start_date'=>$reservation->reservation_start_date,
                'reservation_courses'=>$reservation->courses,
                'reservation_activities'=>$reservation->activities,

                'status' => $reservation->status,
                'reservation_client_end'=> $reservation->client_end,

                'child_id'=> $child->id,
                'child_name'=> $child->name,
                'child_age'=> $child->age,

                'child_gender'=> $child->gender,
            ])
            ->onQueue(config('queue.rabbitmq_queue.provider_service'))
            ->onConnection('rabbitmq');

            return response()->json([
                'message'=> 'Your reservation request will be sent to the nursery.',
                'reservation' => new ReservationIndexResource($reservation),
                'status'=> 201
            ]);



        } catch (Exception $e) {
            return response()->json([
                'errors' => [$e->getMessage()],
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
                if((! $reservation || $reservation->client_id != $request->client_id) && !$request->isAdmin ) {
                    return response()->json([
                        'errors' =>[ 'You can not show this reservation.'],
                        'status' => Response::HTTP_UNAUTHORIZED
                    ]);
                }

                return response()->json([
                    'reservation'=> new ReservationIndexResource($reservation),
                    'status'=> Response::HTTP_ACCEPTED
                ]);
                
		} catch (\Exception $e) {
			return response()->json([
                'errors' => [$e->getMessage()],
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
                    'errors' => ['You can not update this reservation.'],
                    'status'=> Response::HTTP_UNAUTHORIZED
                ]);
			}

            if (in_array($reservation->status, [3, 4, 5, 6, 7, 8])) {
                $status = Reservation::RESERVATION_STATUS[$reservation->status ?? 0];
                return response()->json(
                  [
                    'errors' => ["This reservation is {$status}.You can not update it."],
                    'status' => Response::HTTP_UNAUTHORIZED,
                  ],
                );
            }

            $validator = Validator::make($request->all(), $request->rules());

			if($validator->fails()) {
				return response()->json([
                    'errors' => Arr::flatten($validator->getMessageBag()),
                    'status' =>Response::HTTP_NOT_ACCEPTABLE
                ]);
			}

            $data = $validator->validated();

            
            

            if($request->client_end){
                if(in_array($reservation->status, [0, 1])){
                    if($reservation->status == 1){
                        $reservation->update([
                            'client_end' => 1,
                            'status' => 7,
                          ]);
                    }elseif($reservation->status == 0){
                        $reservation->update([
                            'client_end' => 1,
                            'status' => 3,
                        ]);
                    }
                } else {
                    return response()->json([
                        'message' => 'You can not cancel a non pending reservation.',
                        'status' => Response::HTTP_ACCEPTED
                    ]);
                }

                ClientReservationCancelJob::dispatch(['reservation_id'=> $reservation->id])
                ->onQueue(config('queue.rabbitmq_queue.provider_service'))
                ->onConnection('rabbitmq');

                $reservation->delete();

                return response()->json([
                        'message' => 'Your reply will be sent to nursery',
                        'status' => Response::HTTP_ACCEPTED
                    ]);

            }

            


            $reservation->update(['client_end'=> $data['client_end'] ?? 0 ]);
            return response()->json([
                'message' => 'No Changes',
                'status'=>Response::HTTP_CONTINUE
            ]);

		} catch (Exception $e) {
			return response()->json([
                'errors' => [$e->getMessage()],
                'status' => Response::HTTP_NOT_FOUND
            ]);
		}
    }

    public function reservationByChild(Request $request, $id){
        try {

            $reservation = Reservation::where([['child_id', $id], ['client_id', $request->client_id]])->orderBy('created_at','desc')->first();

        
			if(!$reservation ) {
               
				return response()->json([
                    'errors' =>[ 'You can not show this reservation.'],
                    'status' => Response::HTTP_UNAUTHORIZED
                ]);
			}

			return response()->json([
                'reservation'=> new ReservationIndexResource($reservation),
                'status'=> Response::HTTP_ACCEPTED
            ]);
		} catch (\Exception $e) {
			return response()->json([
                'errors' => [$e->getMessage()],
                'status'=> Response::HTTP_NOT_FOUND
            ]);
		}
    }


    public function reservationsByChild(Request $request, $id){
        try {

            $reservations = Reservation::where([['child_id', $id], ['client_id', $request->client_id]])->withTrashed()->get();

			if(!$reservations ) {
				return response()->json([
                    'errors' =>[ 'You can not show this reservation.'],
                    'status' => Response::HTTP_UNAUTHORIZED
                ]);
			}

			return response()->json([
                'reservation'=> ReservationIndexResource::collection($reservations),
                'status'=> Response::HTTP_ACCEPTED
            ]);

		} catch (\Exception $e) {
			return response()->json([
                'errors' => [$e->getMessage()],
                'status'=> Response::HTTP_NOT_FOUND
            ]);
		}
    }




}
