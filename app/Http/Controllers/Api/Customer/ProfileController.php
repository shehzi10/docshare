<?php

namespace App\Http\Controllers\Api\Customer;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;

class ProfileController extends Controller
{
    public function updateProfile(Request $request)
    {
        $data = $request->except(['profile_pic']);

        if ($request->hasFile('profile_pic')) {
            $file               =   $request->file('profile_pic');
            $fileName = time() . '.' . $request->file('profile_pic')->getClientOriginalExtension();
            $featured_path      =   'public/images';
            $file->move($featured_path, $fileName);
            $data['profile_pic']   =   $fileName;
        }
        User::where('id', $request->user()->id)->update($data);

        $user = User::find($request->user()->id);
        // $user['subs'] = (!empty(NewUserSubscribe::where('user_id', $user->id)->first())) ? NewUserSubscribe::where('user_id', $user->id)->first() : UserSubscription::where('user_id', $user->id)->first();

        if ($user) {
            return apiresponse(true, 'Your Profile has been updated successfully', $user);
        } else {
            return apiresponse(false, 'Error in updating profile');
        }
    }


    public function searchUser(Request $request){

        if($request->search)
        {
            $user = User::where('username', 'LIKE', '%' .$request->search . '%')->orderBy('created_at', 'DESC')->simplePaginate(10);
            return apiresponse(true, 'User with names found', $user);
        }

        else{
            $user = User::orderBy('created_at', 'DESC')->simplePaginate(10);
            return apiresponse(true, 'All Users Found', $user);
        }

    }


    public function logout()
    {
        $user = request()->user();
        User::findOrFail($user->id)->update(['device_id' => null]);
        return apiresponse(true, 'You have been logged out successfully');
    }

}
