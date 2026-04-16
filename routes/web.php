<?php

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


//
//Route::group(['middleware' => 'auth'], function() {
//	Route::get('/dashboard', 'HomeController@dashboard');
//});
//Route::domain('{organization}.{branch}.localhost')
//    ->middleware(['auth', 'active' , 'tenent'])
//    ->group(function() {
//     include('all-routes.php');
//});


Auth::routes();
//Route::group(['prefix' => '/orgtest/{org}' , ['middleware' => ['tenent' ]] , 'as' => 'org.'] ,function() {//, 'auth', 'active'
//
////        dd('with data');
////    dd(request());
//    include('all-routes.php');
//});

//Route::domain('{organization}.localhost')
//    ->middleware(['auth', 'active' , 'tenent'])
//    ->group(function() {
//        include('all-routes.php');
//    });
//

Route::group(['middleware' => ['auth', 'active' , 'expired']] ,function() {
//    dd('without data');
    include('all-routes.php');
});

Route::get('expierd' , 'ExpierdController@index')->name('expierd');

