<?php

namespace App\Http\Controllers\Api\Customer;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\UserFriend;
use Illuminate\Http\Request;
use App\Http\Resources\UserResource;
use Auth;


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
            $user = User::where('username', 'LIKE', '%' .$request->search . '%')->orderBy('created_at', 'DESC')->paginate(2);
            $user = UserResource::collection($user)->response()->getData(true);  
            foreach($user['data'] as $key => $friend){
                $UserFriend = UserFriend::where('user_id',Auth::user()->id)->where('requested_user_id',$friend['id'])->first();
                if($UserFriend){
                    if($UserFriend->status == 'approved'){
                        $user['data'][$key]['is_friend'] = true;
                        $user['data'][$key]['status'] = $UserFriend->status;
                    }else{
                        $user['data'][$key]['is_friend'] = false;
                        $user['data'][$key]['status'] = $UserFriend->status;
                    }
                }else{
                    $user['data'][$key]['is_friend'] = false;
                    $user['data'][$key]['status'] = null;
                }
                
            }
            $user['success'] = true;
            $user['message'] = 'All Users Found';
            return apiresponse_two( $user);
        }
        else{
            $user = User::orderBy('created_at', 'DESC')->paginate(2);
            $user = UserResource::collection($user)->response()->getData(true);
            foreach($user['data'] as $key => $friend){
                $UserFriend = UserFriend::where('user_id',Auth::user()->id)->where('requested_user_id',$friend['id'])->first();
                if($UserFriend){
                    if($UserFriend->status == 'approved'){
                        $user['data'][$key]['is_friend'] = true;
                        $user['data'][$key]['status'] = $UserFriend->status;
                    }else{
                        $user['data'][$key]['is_friend'] = false;
                        $user['data'][$key]['status'] = $UserFriend->status;
                    }
                }else{
                    $user['data'][$key]['is_friend'] = false;
                    $user['data'][$key]['status'] = null;
                }
            }
            $user['success'] = true;
            $user['message'] = 'All Users Found';
            return apiresponse_two( $user);
        }
    }


    public function logout()
    {
        $user = request()->user();
        User::findOrFail($user->id)->update(['device_id' => null]);
        return apiresponse(true, 'You have been logged out successfully');
    }

}
