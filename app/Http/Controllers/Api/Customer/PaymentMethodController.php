<?php

namespace App\Http\Controllers\Api\Customer;

use App\Http\Controllers\Controller;
use App\Models\PaymentMethod;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
use Stripe\StripeClient;

class PaymentMethodController extends Controller
{
    public $stripe = "";
    public function __construct()
    {
        $this->stripe = new StripeClient(env('STRIPE_SECRET_KEY'));
    }

    public function storeCard(Request $request)
    {
        $user =  Auth::user();

        $validator = Validator::make($request->all(), [
            'card_number'       => 'required',
            'exp_date'          => 'required',
            'cvc'               => 'required',
    //            'name'              => 'required',
        ]);

        if ($validator->fails()) {
            return apiresponse(false, implode("\n", $validator->errors()->all()));
        }
        try {
            $stripe = new StripeClient(env("STRIPE_SECRET_KEY"));
            $date = explode("/", $request->exp_date);
            // dd($date);
            $token = $this->stripe->tokens->create([
                'card' => [
                    'number' => $request->card_number,
                    'exp_month' => $date[0],
                    'exp_year' => $date[1],
                    'cvc' => $request->cvc,
                ],
            ]);

            $stripe_customer_id = $user->stripe_customer_id;
            // var_dump($stripe_customer_id);die();
            $stripeCustomer = $this->stripe->customers->retrieve($stripe_customer_id);
            //                return response()->json(["status" => "error", "data" => $stripeCustomer]);
            $willBeDefault = ($stripeCustomer->default_source == null) ? true : false;
            $source = $this->stripe->customers->createSource($stripe_customer_id, [
                'source' => $token
            ]);

            $pm = PaymentMethod::create([
                'user_id'           => $user->id,
                'stripe_source_id'  => $source->id,
                'default_card'      => $willBeDefault,
                'card_name'         => $request->name,
                'card_brand'        => $source->brand,
                'card_end_number'   => $source->last4,
            ]);

            return apiresponse(true, 'Card Added', $pm);
        } catch(Exception $e){
            return apiresponse(false, $e->getMessage());
        }
    }

    public function deleteCard(Request $request){
        try{
            $user = $request->user();
            $stripeUser =  $this->stripe->customers->retrieve($user->stripe_customer_id);
//            return $user;
            if ($stripeUser){
                $this->stripe->customers->deleteSource(
                    $user->stripe_customer_id,
                    $request->stripe_source_id,
                    []
                );
                PaymentMethod::where('stripe_source_id', $request->stripe_source_id)->delete();
            }
            return apiresponse(true, 'Card Deleted');
        }
        catch (\Exception $e){
            return apiresponse(false, $e->getMessage());
        }
    }


    public function updateDefaultCard(Request $request, $id){

        $customer = request()->user();
        PaymentMethod::where(['user_id' => $customer->id])->update(['default_card' => 0]);
        $paymentMethod = PaymentMethod::findOrFail($id);
//        return $paymentMethod;
        $stripe = new StripeClient(env("STRIPE_SECRET_KEY"));
        $stripe->customers->update($customer->stripe_customer_id,[
            'default_source' => $paymentMethod->stripe_source_id
        ]);
        PaymentMethod::find($id)->update(['default_card' => 1]);
        return apiresponse(true,'Default Payment Method Updated!');

    }


    public function showMethod(Request $request){
        $user = $request->user();
        $data = PaymentMethod::with('user')->where('user_id', $user->id)->get();
        if ($data) {
            return apiresponse(true, 'Payment Methods Found', $data);
        } else {
           return apiresponse(false, 'Payment Method not found');
        }
    }

}

