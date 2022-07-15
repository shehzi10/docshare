<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Mail\ForgotPassword;
use App\Models\User;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Validator;
use Stripe\StripeClient;

class AuthController extends Controller
{
    public function register(Request $request){

        $validator = Validator::make($request->all(),[
            'username'  =>  'required',
            'email'     =>  'required|unique:users,email',
            'passowrd'  =>  'min:8|required_with:confirm_password|same:confirm_password',
        ]);
        if($validator->fails()){
            return apiresponse(false, implode("\n", $validator->errors()->all()));
        }

        $stripe = new StripeClient(env("STRIPE_SECRET_KEY"));
        $stripeCustomer = $stripe->customers->create([
            'email' => $request->email,
            'name' => $request->username,
        ]);
        $data = $request->except(['password']);
        $data['stripe_customer_id'] = $stripeCustomer->id;
        $data['password'] = Hash::make($request->password);

        $user = User::create($data);

         $user = User::where('email', $request->email)->first();

         if ($user) {
            if (Hash::check($request->password, $user->password)) {
                if ($request->has('device_id') and !empty($request->device_id)) {
                    User::find($user->id)->update(['device_id' => $request->device_id]);
                    $user = User::find($user->id);
                }
                $data = [
                    'token' => $user->createToken('customer Token')->accessToken,
                    'user' => $user
                ];
                return apiresponse(true, 'Login Success', $data);
            } else {
                return apiresponse(false, 'Invalid Credentials');
            }
        } else {
            return apiresponse(false, 'Please try again');
        }
    }

    public function login(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'email'     => 'required|email|exists:users,email',
            'password'  => 'required|min:8'
        ]);
        if ($validator->fails()) return apiresponse(false, implode("\n", $validator->errors()->all()));
        $user = User::where('email', $request->email)->first();
        // $user = User::where('email', $request->email)->with('userPlan')->with('userSubscription')->first();

        if ($user) {
            if (Hash::check($request->password, $user->password)) {
                if ($request->has('device_id') and !empty($request->device_id)) {
                    User::find($user->id)->update(['device_id' => $request->device_id]);
                    $user = User::find($user->id);
                    // $user = User::with('userPlan')->with('userSubscription')->find($user->id);
                }
                $data = [
                    'token' => $user->createToken('customer Token')->accessToken,
                    'user' => $user
                ];

                return apiresponse(true, 'Login Success', $data);
            } else {
                return apiresponse(false, 'Invalid Credentials');
            }
        } else {
            return apiresponse(false, 'User not Found!');
        }
    }

    public function sendForgotPasswordEmail(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'email' => 'required'
        ]);

        if ($validator->fails()) {
            return apiresponse(false, implode("\n", $validator->errors()->all()));
        } 

        try {   
            $user = User::where('email', $request->email)->first();
            $code = substr(md5(rand()), 0, 4);
            if (!$user) {
                return apiresponse(false, 'Email does not exist');
            } else {
                $user->confirmation_code = $code;
                if($user->save()) {
                    Mail::to($user->email)->send(new ForgotPassword($user));
                    return apiresponse(true, 'Password reset link sent to your email', $user);
                } else {
                    return apiresponse(false, 'Some error occurred. Please try again');
                }
            }
        } catch(Exception $e) {
            return apiresponse(false, $e->getMessage());
        }
    }

    public function verifyForgotPin(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'email' => 'required',
            'code'  => 'required'
        ]);

        if ($validator->fails()) {
            return apiresponse(false, implode("\n", $validator->errors()->all()));
        } 

        $user = User::where('email', $request->email)->first();
        if($request->code == $user->confirmation_code){
            return apiresponse(true, 'Confirmation code has been matched successfully', $user);
        }

        return apiresponse(false, 'Code missmatch');
    }

    public function resetPassword(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'email' => 'required',
            'password' => 'required',
            'password_confirm' => 'required|same:password'
        ]);

        if ($validator->fails()) {
            return apiresponse(false, implode("\n", $validator->errors()->all()));
        }

        try {
            $pass = Hash::make($request->password);
            User::where('email', $request->email)->update(['password' => $pass]);
            return apiresponse(true, "password updated successfully");
        } catch (Exception $e) {
            return apiresponse(false, $e->getMessage());
        }
    }

}
