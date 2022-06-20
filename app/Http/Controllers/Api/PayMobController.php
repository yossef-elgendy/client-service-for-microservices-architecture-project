<?php

namespace App\Http\Controllers\Api;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Jobs\ClientDispatched\ClientSubscriptionCreateJob;
use App\Jobs\ClientDispatched\ClientSubscriptionRenewJob;
use App\Models\Client;
use App\Models\Order;
use App\Models\Subscription;
use BaklySystems\PayMob\Facades\PayMob;
use Illuminate\Support\Facades\Validator as Validator;
use Symfony\Component\HttpFoundation\Response ;

class PayMobController extends Controller
{
    protected $renew = false ;
    /**
     * Display checkout page.
     *
     * @param  int  $orderId
     * @return Response
     */
    public function checkingOut($orderId, $payment_method = 0)
    {
        $order       = Order::find($orderId);
        $reservation = $order->reservation;
        $user = Client::findOrFail($reservation->client_id);
        
        if($order->status == 1 ){
            return response()->json([
                'message' => "This reservation has already been paid for",
                'status' => Response::HTTP_OK,
            ]);
        }

        if($payment_method == 1){
            $fullname = explode(' ', $user->fullname);
            # code... get order user.
            $auth        = PayMob::authPaymob(); // login PayMob servers
            if (property_exists($auth, 'detail')) { // login to PayMob attempt failed.
                # code... redirect to previous page with a message.
                return response()->json([
                    'message' => $auth->detail,
                    'status' => Response::HTTP_UNAUTHORIZED,
                ]);
            }
            $paymobOrder = PayMob::makeOrderPaymob( // make order on PayMob
                $auth->token,
                $auth->profile->id,
                intval($order->totalCost * 100),
                $order->id
            );

            // Duplicate order id
            // PayMob saves your order id as a unique id as well as their id as a primary key, thus your order id must not
            // duplicate in their database.
            if (isset($paymobOrder->message)) {
                if ($paymobOrder->message == 'duplicate') {
                    $iframe_url = "https://accept.paymob.com/api/acceptance/iframes/362203?payment_token=".$order->payment_key;
                    return response()->json( [
                        'message' => $paymobOrder->message,
                        'iframe_url' => $iframe_url,
                        'payment_token'=>$order->payment_key,
                        'status' => Response::HTTP_ALREADY_REPORTED
                        ]);
                }
            }

            // save paymob order id for later usage.
            $payment_key = PayMob::getPaymentKeyPaymob( // get payment key
                $auth->token,
                $order->totalCost * 100,
                $paymobOrder->id,
                // For billing data
                $user->email, //optional
                $fullname[0], //firstname optional
                $fullname[count($fullname)-1], // lastname optional
                $user->phone, //optional

            );

            $order->update([
                'paymob_order_id' => $paymobOrder->id,
                'payment_key' => $payment_key->token
            ]);

            $iframe_url ="https://accept.paymob.com/api/acceptance/iframes/405720?payment_token=".$payment_key->token;
            return response()->json([
                'iframe_url' => $iframe_url,
                'payment_token'=> $payment_key->token,
                'status' => Response::HTTP_ACCEPTED,
            ]);

            
        } else if($payment_method == 0 || !$payment_method){
            $subscription = Subscription::updateOrCreate([
                'reservation_id' => $reservation->id,
                'child_id'=> $reservation->child_id,
                'nursery_id'=> $reservation->nursery_id,
            ],[
                'start_date' => $reservation->reservation_start_date,
                'due_date' => date('Y-m-d', strtotime($reservation->reservation_start_date. ' + 1 months')),
                'payment_date' => null,
                'payment_method' => $payment_method ?? 0,
                'status' => 0
            ]);

            if(!$this->renew){
                ClientSubscriptionCreateJob::dispatch([
                    'reservation_id'=> $reservation->id,
                    'subscription_id' => $subscription->id,
                    'subscription_due_date' => $subscription->due_date,
                    'subscription_payment_date' => $subscription->payment_date,
                    'subscription_payment_method' => $subscription->payment_method,
                    'subscription_status' => $subscription->status
                ])->onQueue(config('queue.rabbitmq_queue.provider_service'))
                ->onConnection('rabbitmq');
                return response()->json([
                    'message' => "Subscription has been created, please confirm the payment with your provider.",
                    'status' => Response::HTTP_OK,
                    'payment_status' => 0
                ]);
            } else {
                ClientSubscriptionRenewJob::dispatch([
                    'subscription_id'=> $subscription->id,
                    'start_date' => $subscription->start_date,
                    'due_date' => date('Y-m-d', strtotime($subscription->start_date. ' + 1 months')),
                    'payment_date' => null,
                    'payment_method' => $subscription->payment_method,
                    'status' => 0,
                ])->onQueue(config('queue.rabbitmq_queue.provider_service'))
                ->onConnection('rabbitmq');
                return response()->json([
                    'message' => "Subscription has been renewed, please confirm the payment with your provider.",
                    'status' => Response::HTTP_OK,
                    'payment_status' => 0
                ]);
            }
            

        } else {
            return response()->json([
                'message' => "Unauthorized link to access payment.",
                'status' => Response::HTTP_NOT_FOUND,
            ]);
        }
    }

    public function renewSub($orderId, $payment_method = 0){
        $old_order = Order::Find($orderId);
        $order = Order::create([
            'reservation_id'=> $old_order->reservation_id,
            'totalCost'=> $old_order->totalCost
        ]);
        $old_order->delete();
        return $this->checkingOut($order->id, $payment_method);
    }


    /**
     * Make payment on PayMob for API (mobile clients).
     * For PCI DSS Complaint Clients Only.
     *
     * @param  \Illuminate\Http\Reuqest  $request
     * @return Response
     */
    public function payAPI(Request $request, $type)
    {
        if($type == "web"){
            $rules = [
                'orderId'         => 'required|integer',
                'client_id'       => 'required|exists:clients,id',
            ];
        }

        if($type == "mobile"){
            $rules = [
                'orderId'         => 'required|integer',
                'client_id'       => 'required|integer',
                'card_number'     => 'required|numeric|digits:16',
                'card_holdername' => 'required|string|max:255',
                'card_expiry_mm'  => 'required|integer|max:12',
                'card_expiry_yy'  => 'required|integer',
                'card_cvn'        => 'required|integer|digits:3',
            ];
        }

        Validator::make($request->all(), $rules);
        $checkingOutDetails = json_decode($this->checkingOut($request->orderId)->getContent());

        if($checkingOutDetails->status == Response::HTTP_UNAUTHORIZED){
            return response()->json([
                'errors' => 'You are not authorized',
                'status' => $checkingOutDetails->status
            ]);
        }
        
        if($type == "web"){
            return response()->json([
                'iframe_url' => $checkingOutDetails->iframe_url,
                'status' => $checkingOutDetails->status
            ]);
        } else if ($type == "mobile") {
            $user    = Client::find($request->client_id);
            $fullname = explode(' ', $user->fullname);
            $order   = Order::findOrFail($request->orderId);
            $payment = PayMob::makePayment( // make transaction on Paymob servers.
              $checkingOutDetails->payment_token,
              $request->card_number,
              $request->card_holdername,
              $request->card_expiry_mm,
              $request->card_expiry_yy,
              $request->card_cvn,
              $order->paymob_order_id,
              $fullname[0],
              $fullname[count($fullname)-1],
              $user->email,
              $user->phone
            );
        }

        

        # code...
    }

    /**
     * Transaction succeeded.
     *
     * @param  object  $order
     * @return void
     */
    protected function succeeded($order)
    {
        $order->update([
            'status' => 1
        ]);
    }

    /**
     * Transaction voided.
     *
     * @param  object  $order
     * @return void
     */
    protected function voided($order)
    {
        # code... Future Work
    }

    /**
     * Transaction refunded.
     *
     * @param  object  $order
     * @return void
     */
    protected function refunded($order)
    {
        $order->update([
            'status' => 2
        ]);
    }

    /**
     * Transaction failed.
     *
     * @param  object  $order
     * @return void
     */
    protected function failed($order)
    {
        # code... Future Work
    }

    /**
     * Processed callback from PayMob servers.
     * Save the route for this method in PayMob dashboard >> processed callback route.
     *
     * @param  \Illumiante\Http\Request  $request
     * @return  Response
     */
    public function processedCallback(Request $request)
    {

        $orderId = $request->order;

        $order   = Order::where('paymob_order_id', $orderId)->first();
        $reservation = $order->reservation ;


        // Statuses.
        $isSuccess  = $request->success == "false"? false : true;
        $isVoided   = $request->is_voided == "false"? false : true;
        $isRefunded = $request->is_refunded == "false"? false : true;

        if ($isSuccess && !$isVoided && !$isRefunded) {
            $subscription = Subscription::updateOrCreate([
                'reservation_id' => $reservation->id,
                'child_id'=> $reservation->child_id,
                'nursery_id'=> $reservation->nursery_id,
            ],[
                'start_date' => $reservation->reservation_start_date,
                'due_date' => date('Y-m-d', strtotime($reservation->reservation_start_date. ' + 1 months')),
                'payment_date' => now(),
                'payment_method' => 1,
                'status' => 1
            ]);
            if(!$this->renew){
                ClientSubscriptionCreateJob::dispatch([
                    'reservation_id'=> $reservation->id,
                    'subscription_id' => $subscription->id,
                    'subscription_due_date' => $subscription->due_date,
                    'subscription_payment_date' => $subscription->payment_date,
                    'subscription_payment_method' => $subscription->payment_method,
                    'subscription_status' => $subscription->status
                ])->onQueue(config('queue.rabbitmq_queue.provider_service'))
                ->onConnection('rabbitmq');
            }else {
                ClientSubscriptionRenewJob::dispatch([
                    'subscription_id'=> $subscription->id,
                    'start_date' => $subscription->start_date,
                    'due_date' => date('Y-m-d', strtotime($subscription->start_date. ' + 1 months')),
                    'payment_date' => now(),
                    'payment_method' => $subscription->payment_method,
                    'status' => 0,
                ])->onQueue(config('queue.rabbitmq_queue.provider_service'))
                ->onConnection('rabbitmq');
            }
           
             // transcation succeeded.
            $this->succeeded($order);
            return response()->json(['success' => $isSuccess , "status"=> Response::HTTP_ACCEPTED]);

        } elseif ($isSuccess && $isVoided) { // transaction voided.
            $this->voided($order);
            return response()->json(['is_voided' => $isVoided, "status"=> Response::HTTP_ACCEPTED]);

        } elseif ($isSuccess && $isRefunded) { // transaction refunded.
            $this->refunded($order);
            return response()->json(['is_refunded' => $isRefunded, "status"=> Response::HTTP_ACCEPTED]);

        } elseif (!$isSuccess) { // transaction failed.
            $this->failed($order);
            return response()->json(['success' => $isSuccess, "status"=> Response::HTTP_INTERNAL_SERVER_ERROR]);
        }

        return response()->json(['success' => $isSuccess], 200);
    }

    /**
     * Display invoice page (PayMob response callback).
     * Save the route for this method to PayMob dashboard >> response callback route.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return Response
     */
    public function invoice(Request $request)
    {
        # code...
    }

}
