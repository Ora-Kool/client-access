<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Http\Request;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/
Route::get('/', function () {
    return view('home');
});

Route::get('/redirect', function () {
    $query = http_build_query([
        'client_id' => env('PASSPORT_CLIENT_ID'),
        'redirect_uri' => env('APP_URL').'/callback',
        'response_type' => 'token',
        'scope' => '',
    ]);

    return redirect(env('BOOK_STORE_URL').'/oauth/authorize?'.$query);
});

Route::get('/callback', function (Request $request) {
    dd();
    if ($request->has("error") && $request->get("error") == "access_denied"){
        return redirect('/');
    }
    $http = new GuzzleHttp\Client;


    $response = $http->post(env('BOOK_STORE_URL').'/oauth/token', [
        'form_params' => [
            'grant_type' => 'authorization_code',
            'client_id' => env('PASSPORT_CLIENT_ID'),
            'client_secret' => env('PASSPORT_CLIENT_SECRET'),
            'redirect_uri' => env('APP_URL').'/callback',
            'code' => $request->code,
        ],
    ]);
    $tokens = json_decode((string) $response->getBody(), true);
    $user = fetchUser($tokens['access_token'], $http);

    return view('authenticated', array_merge($tokens, $user));
});

function fetchUser($accessToken, $http){
    $response = $http->get(env('BOOK_STORE_URL').'/api/user', [
        'headers' =>[
            'Accept' => 'application/json',
            'Authorization' => 'Bearer '.$accessToken,
        ]
    ]);

    return json_decode((string) $response->getBody(), true);
}
