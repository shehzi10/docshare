<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/

// Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
//     return $request->user();
// });



Route::group(['namespace' => 'App\Http\Controllers\Api'], function () {


    Route::post('register', 'AuthController@register');
    Route::post('login', 'AuthController@login');


});


Route::group(['namespace' => 'App\Http\Controllers\Api\Customer', 'middleware' => 'auth:api'], function () {

    Route::get('logout', 'ProfileController@logout');
    Route::post('updateProfile', 'ProfileController@updateProfile');
    Route::post('searchUser', 'ProfileController@searchUser');



    //Post Module
    Route::get('getPosts','PostController@index')->name('getPosts');
    Route::post('uploadPost','PostController@store')->name('uploadPost');
    Route::post('openDocument','PostController@openDocument')->name('openDocument');
    Route::post('deletePost','PostController@destroy')->name('deletePost');
    Route::get('getAllDocuments','PostController@getAllDocuments')->name('getAllDocuments');


    // USER FRIENDS
    Route::post('addFriend', 'FriendListController@addFriend');
    Route::get('getAllFriendsRequests', 'FriendListController@getAllFriendsRequests');
    Route::post('sendRequest', 'FriendListController@sendRequest');
    Route::post('acceptFriendRequest', 'FriendListController@acceptFriendRequest');
    Route::post('rejectFriendRequest', 'FriendListController@rejectFriendRequest');
    Route::get('unFriendUser/{id}', 'FriendListController@unFriendUser');



    // SETTINGS
    Route::post('toggleNotification', 'SettingsController@toggleNotification');
    Route::post('changePassword/{id}', 'SettingsController@changePassword');



    // MESSAGES
    Route::post('chatIndex', 'ChatsController@index');
    Route::post('chatSession', 'ChatsController@checkSessionBeforeMessage');
    Route::get('viewChatlist/{id}', 'ChatsController@show');
    Route::post('sendMessage', 'ChatsController@sendMessage');

    // CARD MODULE
    Route::post('storeCard', 'PaymentMethodController@storeCard');
    Route::post('updateDefaultCard/{id}', 'PaymentMethodController@updateDefaultCard');
    Route::get('showMethod', 'PaymentMethodController@showMethod');
    Route::post('deleteCard', 'PaymentMethodController@deleteCard');


    // PAGES
    Route::get('data', 'PagesController@data');

});
