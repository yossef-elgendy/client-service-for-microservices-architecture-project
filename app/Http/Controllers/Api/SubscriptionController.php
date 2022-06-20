<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Controllers\PayMobController;
use App\Http\Requests\UpdateSubscriptionRequest;
use App\Http\Resources\Subscription\SubscriptionIndexResoruce;
use App\Jobs\ClientDispatched\ClientCancelSubscription;
use App\Jobs\ClientDispatched\ClientRenewSubscription;
use App\Jobs\ClientDispatched\ClientSubscriptionCancelJob;
use App\Jobs\ClientDispatched\ClientSubscriptionRenewJob;
use App\Models\Child;
use App\Models\Subscription;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Symfony\Component\HttpFoundation\Response;

class SubscriptionController extends Controller
{
    public function index(Request $request)
  {
    try {
        $children = Child::where('client_id', $request->client_id)->get();
        $subscriptions = [];
        foreach($children as $child){
            array_push($subscriptions, $child->subscription());
        }

        return response()->json([
          'data' => SubscriptionIndexResoruce::collection($subscriptions),
          'status' => Response::HTTP_OK,
        ]);

    } catch (\Exception $e) {
      return response()->json([
          'errors' => [$e->getMessage()],
          'status' => Response::HTTP_NOT_FOUND,
        ]);
    }
  }

  public function show($id)
  {
    try {

        if(! $subscription = Subscription::find($id)) {
            return response()->json([
                'errors' => ['You can not show this subscription.'],
                'status' => Response::HTTP_UNAUTHORIZED,
            ]);
        }

        return response()->json([
            'data' => new SubscriptionIndexResoruce($subscription),
            'status' => Response::HTTP_OK,
            ]);

    } catch (\Exception $e) {
      return response()->json([
          'errors' => [$e->getMessage()],
          'status' => Response::HTTP_NOT_FOUND,
        ]);
    }
  }

  public function update(UpdateSubscriptionRequest $request, $id)
  {
    try {
			if(! $subscription = Subscription::find($id)) {
                return response()->json([
                    'errors' => ['You can not update this subscription.'],
                    'status' => Response::HTTP_UNAUTHORIZED,
                ]);
            }

			if($subscription->child->client_id !== $request->client_id) {
                return response()->json([
                    'errors' => ['You can not update this subscription.'],
                    'status' => Response::HTTP_UNAUTHORIZED,
                ]);
			}

			$validator = Validator::make($request->all(), $request->rules());

			if($validator->fails()) {
                return response()->json([
                    'errors' => $validator->getMessageBag(),
                    'status' => Response::HTTP_NOT_ACCEPTABLE,
                ]);
			}

			$data = $validator->validated();

            $subscription->update([
                'start_date'=> $data['start_date'],
                'due_date'=>date('Y-m-d', strtotime($subscription->start_date. ' + 1 months')),
                'payment_date'=>null,
                'status' => 0
            ]);
            
            $pay_mob = new PayMobController();
            $result = $pay_mob->renewSub($subscription->reservation->order->id, $subscription->payment_method);
            $result = json_decode($result->getContent());

            return response()->json([
                'data' => new SubscriptionIndexResoruce($subscription),
                'message'=> $result->message,
                'payment_status'=> Subscription::SUBSCRIPTION_STATUS[$result->payment_status]??Subscription::SUBSCRIPTION_STATUS[0],
                'status' => $result->status,
            ]);

		} catch (\Exception $e) {
            return response()->json([
                'errors' => [$e->getMessage()],
                'status' => Response::HTTP_NOT_FOUND,
                ]);
		}
  }



  public function destroy(Request $request, $id){
    try{

        if(! $subscription = Subscription::find($id)) {
            return response()->json([
                'errors' => ['You can not update this subscription.'],
                'status' => Response::HTTP_UNAUTHORIZED,
            ]);
        }

        if($subscription->child()->client_id !== $request->client_id) {
            return response()->json([
                'errors' => ['You can not update this subscription.'],
                'status' => Response::HTTP_UNAUTHORIZED,
            ]);
        }

        ClientSubscriptionCancelJob::dispatch(['subscription_id' => $subscription->id])
        ->onQueue(config('queue.rabbitmq_queue.provider_service'))
        ->onConnection('rabbitmq');

        $subscription->delete();
        return response()->json([
            'message' => 'Your subscription has been canceled',
            'status'=> Response::HTTP_OK
        ]);

    } catch(\Exception $e){
        return response()->json([
            'errors' => [$e->getMessage()],
            'status' => Response::HTTP_NOT_FOUND,
            ]);
    }
  }
}
