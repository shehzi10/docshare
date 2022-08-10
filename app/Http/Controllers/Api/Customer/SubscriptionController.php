<?php

namespace App\Http\Controllers\Api\Customer;

use App\Http\Controllers\Controller;
use App\Models\PaymentMethod;
use App\Models\Subscription;
use App\Models\User;
use App\Models\UserSubscription;
use Illuminate\Http\Request;
use Stripe\StripeClient;
use App\Http\Resources\SubscriptionResource;


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
            $userSubscription = UserSubscription::where('user_id',$user->id)->where('status',true)->get();
            if($userSubscription != null){
                foreach($userSubscription as $sub){
                    $this->stripe->subscriptions->cancel(
                        $sub->subscription_id,
                        []
                    );
                    $sub->status = 0;
                    $sub->save();
                }
            }
            $subscribe = $this->stripe->subscriptions->create([
                'customer' => $user->stripe_customer_id,

                'items' => [
                    ['price' => $request->plan_id],
                ],
            ]);
            $plan = Subscription::where(['plan_id' => $request->plan_id])->first();
            if($subscribe->id){
                $packages = UserSubscription::create([
                    'user_id'                   =>  $user->id,
                    'plan_id'                   =>  $plan->plan_id,
                    'subscription_id'           =>  $subscribe->id,
                    'subscriptions_id'          =>  $plan->id,
                    'price'                     =>  $plan->price,
                    'payment_method_id'         =>  $payment->stripe_source_id,
                    'status'                    =>  1,
    
                ]);
                $user = User::with(['userSubscription' => function($q){
                    $q->with('subscription')->get();
                }])->find($user->id);
              return apiresponse(true,'Subscription Successfull', $user);
            }
            
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
        $packages = SubscriptionResource::collection($packages);
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
