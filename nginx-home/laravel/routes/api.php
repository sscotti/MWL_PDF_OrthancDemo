<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Auth;
#added Auth SDS

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

Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});
//a47VSfjqKSjNqXb1A7chpyCLC8PyZByGNqnsAeuy
Route::middleware('auth:sanctum')->post('/studies', function (Request $request) {
    return $request->user()->tokenCan('read');
});

Route::middleware(['auth:sanctum'])->post('/PACSUploadStudies/PACSupload', function(Request $request) {

     $PACSUpload = new PACSUploadStudies($request,'PACSupload');
     echo $PACSUpload->get_json_response();


});

