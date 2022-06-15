<?php

namespace App\Http\Controllers\Api\Customer;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;

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


    public function changePassword(Request $request, $id){
        $validator = Validator::make($request->all(), [
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
                $user = User::findOrFail($id)->update($data);
                if ($user) {
                    return apiresponse(true, 'Password has been updated successfully', $data);
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
