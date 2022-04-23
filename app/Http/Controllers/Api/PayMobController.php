<?php

namespace App\Http\Controllers\Api;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Jobs\ClientDispatched\ClientPaid;
use App\Models\Client;
use App\Models\Order;
use BaklySystems\PayMob\Facades\PayMob;
use Facade\FlareClient\Http\Response;
use Illuminate\Support\Facades\Validator as Validator;

class PayMobController extends Controller
{

    /**
     * Display checkout page.
     *
     * @param  int  $orderId
     * @return Response
     */
    public function checkingOut($orderId)
    {
        $order       = Order::find($orderId);
        $user = Client::findOrFail($order->reservation->client_id);
        $fullname = explode(' ', $user->fullname);
        # code... get order user.
        $auth        = PayMob::authPaymob(); // login PayMob servers
        if (property_exists($auth, 'detail')) { // login to PayMob attempt failed.
            # code... redirect to previous page with a message.
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
                return response()->json([
                    'message' => $paymobOrder->message,
                    'iframe_url' => $iframe_url
                    ], 206);
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

        $iframe_url ="https://accept.paymob.com/api/acceptance/iframes/362203?payment_token=".$payment_key->token;
        return response()->json([
            'iframe_url' => $iframe_url
            ], 201);
    }


    /**
     * Make payment on PayMob for API (mobile clients).
     * For PCI DSS Complaint Clients Only.
     *
     * @param  \Illuminate\Http\Reuqest  $request
     * @return Response
     */
    public function payAPI(Request $request)
    {
        Validator::make($request->all(), [
            'orderId'         => 'required|integer',
            'card_number'     => 'required|numeric|digits:16',
            'card_holdername' => 'required|string|max:255',
            'card_expiry_mm'  => 'required|integer|max:12',
            'card_expiry_yy'  => 'required|integer',
            'card_cvn'        => 'required|integer|digits:3',
        ]);

        $user    = auth()->user();
        $order   = Order::findOrFail($request->orderId);
        $payment = PayMob::makePayment( // make transaction on Paymob servers.
    //      $payment_key_token,
          $request->card_number,
          $request->card_holdername,
          $request->card_expiry_mm,
          $request->card_expiry_yy,
          $request->card_cvn,
          $order->paymob_order_id,
          $user->firstname,
          $user->lastname,
          $user->email,
          $user->phone
        );

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
        # code...
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
        # code...
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

        // Statuses.
        $isSuccess  = $request->success == "false"? false : true;
        $isVoided   = $request->is_voided == "false"? false : true;
        $isRefunded = $request->is_refunded == "false"? false : true;

        if ($isSuccess && !$isVoided && !$isRefunded) { // transcation succeeded.
            $this->succeeded($order);
            ClientPaid::dispatch($order->reservation_id);
            return response()->json(['success' => $isSuccess], 200);

        } elseif ($isSuccess && $isVoided) { // transaction voided.
            $this->voided($order);
            return response()->json(['is_voided' => $isVoided], 200);

        } elseif ($isSuccess && $isRefunded) { // transaction refunded.
            $this->refunded($order);
            return response()->json(['is_refunded' => $isRefunded], 200);

        } elseif (!$isSuccess) { // transaction failed.
            $this->failed($order);
            return response()->json(['success' => $isSuccess], 500);
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
