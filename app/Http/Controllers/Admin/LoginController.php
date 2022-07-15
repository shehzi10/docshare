<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class LoginController extends Controller
{
    public function login(){

        return view('admin.login.login');

    }


    public function doLogin(Request $request){

        $user = User::where('email',$request->email)->first();
        if($user){
            $remember_me = $request->has('remember') ? true : false;
            if (Auth::attempt(['email' => $request->email,'password' => $request->password],$remember_me)) {
                if (Auth::user()) {
                    User::find(Auth::id());
                    // die('123123');
                    return redirect(route('users'));
                } else {
                    session()->flash('success', 'Invalid Credentials!');
                    return redirect()->back()->withInput();
                }
            }
        }
        session()->flash('error', 'Invalid Credentials!');
        return redirect()->back()->withInput();


    }
}
