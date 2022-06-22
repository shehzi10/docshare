<?php

namespace App\Http\Controllers\Api\User;

use App\Http\Controllers\Controller;
use App\Models\PaymentMethod;
use App\Models\Subscription;
use App\Models\User;
use App\Models\UserSubscription;
use Illuminate\Http\Request;
use Stripe\StripeClient;

class SubscriptionController extends Controller
{
    public $status = 200;
    public $stripe = "";
    public function __construct()
    {
        $this->stripe = new StripeClient(env('STRIPE_SECRET_KEY'));
    }

    public function subscribe(Request $request)
    {
        if ($request->user_id) {
            $id = $request->user_id;
        } else {
            $id = $request->user()->id;
        }
        $user = User::where("id", $id)->first();

        try {

         $payment = PaymentMethod::where(['user_id' => $id, 'default_card'=> '1'])->first();
        //  dd($payment);
         if ($request->source_id == "") {
             $date = explode("/", $request->exp_date);

             $token = $this->stripe->tokens->create([
                 'card' => [
                     'number' => $request->card_number,
                     'exp_month' => $date[0],
                     'exp_year' => $date[1],
                     'cvc' => $request->cvc,
                    ],
                ]);

                $stripe_customer_id = $user->stripe_customer_id;
                $stripeCustomer = $this->stripe->customers->retrieve($stripe_customer_id);
               //                return response()->json(["status" => "error", "data" => $stripeCustomer]);
               $willBeDefault = ($stripeCustomer->default_source == null) ? true : false;
               $source = $this->stripe->customers->createSource($stripe_customer_id, [
                   'source' => $token
               ]);
               //                echo"<pre>"; print_r($token); die();
               $pm = PaymentMethod::create([
                   'card_brand' => $source->brand,
                   'stripe_source_id' => $source->id,
                   'card_end_number' => $source->last4,
                   'user_id' => $user->stripe_customer_id,
                   'default_card' => $willBeDefault,
               ]);
               $request->source_id = $source->id;
           }
            $subscribe = $this->stripe->subscriptions->create([
                'customer' => $user->stripe_customer_id,

                'items' => [
                    ['price' => $request->plan_id],
                ],
            ]);
            $plan = Subscription::where(['plan_id' => $request->plan_id])->first();
            $packages = UserSubscription::create([
                'user_id'                   =>  $user->id,
                'plan_id'                   =>  $plan->id,
                'price'                     =>  $plan->price,
                'payment_method_id'         =>  $payment->stripe_source_id,

            ]);
            // var_dump($packages);die();
            $user = User::where("users.id", '=', $user->id)->first();
//                ->leftjoin('user_subscriptions', "users.id", '=', "user_subscriptions.user_id")
//                ->select("users.*", "user_subscriptions.id as subscription_package_id")

          return apiresponse(true,'Subscription Successfull', $user);
        } catch (\Exception $e) {
            // return var_dump($user);
           return apiresponse(false, $e->getMessage());
        }
    }


    public function getAllPackages(Request $request){

        $packages = Subscription::get();
        if($packages->count() > 0){
        foreach($packages as $key => $package ){
            $subscription = UserSubscription::where('user_id', $request->user()->id)->where('plan_id', $package->id)->first();
            $packages[$key]['is_subscribed'] = false;
            if($subscription != null){
                $packages[$key]['is_subscribed'] = true;
            }
        }
    }
        return apiresponse(true, 'Packages Found',$packages);
    }


    public function getSubHistory(Request $request){
        $user = request()->user();
        $history = UserSubscription::where('user_id', $user->id)->with('subscription')->orderBy('created_at', 'DESC')->get();
        return apiresponse(true, 'Subscrtiption History Found', $history);
    }



    // public function refundd(Request $request){
    //     require_once('vendor/autoload.php');
    //     $stripe = new StripeClient('STRIPE_SECRET_KEY');

    //     $refunded = $stripe->refunds->create([
    //         'charge'    =>  'card_1L2VybIZd7quGWDZ1ORUIMte',
    //         'amount'    =>  '25.99',
    //     ]);
    //     return apiresponse(true, 'Payment refunded to customer');

    // }
}
