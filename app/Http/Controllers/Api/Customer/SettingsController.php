<?php

namespace App\Http\Controllers\Api\Customer;

use App\Http\Controllers\Controller;
use App\Models\{User,Notification};
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use App\Http\Resources\NotificationResource;


class SettingsController extends Controller
{
    public function toggleNotification(Request $request){
        $user = request()->user();
        $user = User::where('id', $user->id)->update(['is_notify' => $request->is_notify]);

        if($request->is_notify == '1'){
        return apiresponse(true,'Notifications are ON!');
        }
        else{
            return apiresponse(false, 'Notifications are OFF!');
        }
    }

    public function getNotifications(){
        $notifications = Notification::where('reciever_id',request()->user()->id)->orderby('id','DESC')->paginate(10);
        $notification = NotificationResource::collection($notifications)->response()->getData(true);
        // Notification::update(['is_read' => 1]);
        Notification::where('reciever_id',request()->user()->id)
        ->update([
            'is_read' =>  1,
        ]);
        return apiresponse(true, 'Notifications found' ,$notification);
    }

    public function onOffNotification(Request $request){
        $validator = Validator::make($request->all(), [
            'is_notify'       => 'required',
        ]);

        if ($validator->fails()) {
            return apiresponse(false, implode("\n", $validator->errors()->all()));
        }
        $user = User::find(Auth::user()->id);
        $user->is_notify = $request->is_notify;
        if($user->save()){
            return apiresponse(true,'Notification status changed');
        }else{
            return apiresponse(false,'Something went wrong');
        }
    }


    public function changePassword(Request $request){
        $validator = Validator::make($request->all(), [
            'id'                    =>          'required',
            'old_password'          =>          'required_with:password|min:8',
            'new_password'          =>          'min:8|required_with:confirm_password|same:confirm_password|different:old_password',
        ]);
        if ($validator->fails()) {
            return apiresponse(false, implode("\n", $validator->errors()->all()));
        }
        try {
            $old_password   =   Hash::check($request->old_password, Auth::User()->password);
            if ($old_password) {
                $data['password']       =   Hash::make($request->new_password);
                $user = User::findOrFail($request->id)->update($data);
                if ($user) {
                    return apiresponse(true, 'Password has been updated successfully');
                } else {
                    return apiresponse(false, 'Error occurred, please try again');
                }
            } else {
                return apiresponse(false, "Old password is incorrect");
            }
        } catch (\Exception $e) {
            return response()->json(['status' => 'error', 'msg' => $e->getMessage()]);
        }
    }
}
