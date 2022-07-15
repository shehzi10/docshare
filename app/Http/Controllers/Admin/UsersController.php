<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;

class UsersController extends Controller
{
    public function users(){
        $user = User::orderBy('created_at', 'DESC')->where('email', '!=', 'admin@docshare.com')->simplePaginate(10);
        return view('admin.users.user',['users'=>$user]);
    }
}
