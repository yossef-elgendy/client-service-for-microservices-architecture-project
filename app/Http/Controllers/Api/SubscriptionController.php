<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Controllers\PayMobController;
use App\Http\Requests\UpdateSubscriptionRequest;
use App\Http\Resources\Subscription\SubscriptionIndexResoruce;
use App\Jobs\ClientDispatched\ClientSubscriptionCancelJob;
use App\Models\Client;
use App\Models\Subscription;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Validator;
use Symfony\Component\HttpFoundation\Response;

class SubscriptionController extends Controller
{
    public function index(Request $request)
  {
    try {
   
        $client = Client::findOrFail($request->client_id);
        $subscriptions =  $client->children()
        ->whereHas('subscription')
        ->with('subscription')
        ->get()
        ->pluck('subscription'); 
       

        return response()->json([
          'subsriptions' => SubscriptionIndexResoruce::collection($subscriptions),
          'status' => Response::HTTP_OK,
        ]);

    } catch (\Exception $e) {
      return response()->json([
          'errors' => [$e->getMessage()],
          'status' => Response::HTTP_NOT_FOUND,
        ]);
    }
  }

  public function show(Request $request, $id)
  {
    try {
        $subscription = Subscription::find($id);
        if(! $subscription || $subscription->reservation->client_id != $request->client_id) {
            return response()->json([
                'errors' => ['You can not show this subscription.'],
                'status' => Response::HTTP_UNAUTHORIZED,
            ]);
        }

        return response()->json([
            'subscription' => new SubscriptionIndexResoruce($subscription),
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
                    'errors' => Arr::flatten($validator->getMessageBag()),
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

        $subscription = Subscription::find($id);
        if(! $subscription || $subscription->reservation->client_id != $request->client_id) {
            return response()->json([
                'errors' => ['You can not delete this subscription.'],
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



  public function subscriptionsByChild(Request $request, $id)
  {
    try {
        $subscriptions = Subscription::where('child_id', $id)->whereHas('reservation',
        function(Builder $query) use($request){
            $query->withTrashed()->where('client_id', $request->client_id);
        })
        ->orderBy('created_at', 'desc')
        ->withTrashed()
        ->get();
        

        return response()->json([
            'subscritpions' => SubscriptionIndexResoruce::collection($subscriptions),
            'status' => Response::HTTP_OK,
            ]);

    } catch (\Exception $e) {
      return response()->json([
          'errors' => [$e->getMessage()],
          'status' => Response::HTTP_NOT_FOUND,
        ]);
    }
  }


  public function subscriptionByChild(Request $request, $id)
  {
    try {
        $subscription = Subscription::where('child_id', $id)->whereHas('reservation',
        function(Builder $query) use($request){
            $query->withTrashed()
            ->Where([['client_id', $request->client_id], ['status', 5]]);
        })
        ->orderBy('created_at', 'desc')
        ->first();

        return response()->json([
            'subscription' => $subscription ? new SubscriptionIndexResoruce($subscription) : null,
            'status' => Response::HTTP_OK,
        ]);

    } catch (\Exception $e) {
      return response()->json([
          'errors' => [$e->getMessage()],
          'status' => Response::HTTP_NOT_FOUND,
        ]);
    }
  }
}
