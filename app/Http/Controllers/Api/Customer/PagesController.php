<?php

namespace App\Http\Controllers\Api\Customer;

use App\Http\Controllers\Controller;
use App\Models\Page;
use Illuminate\Http\Request;

class PagesController extends Controller
{
    public function data(){
        $page = Page::first();
        return apiresponse(true, 'Pages Data Found', $page);
    }
}
