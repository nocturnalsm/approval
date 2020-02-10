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

Route::prefix('approval')->group(function() {
    Route::get('/', 'ApprovalController@index');
    Route::get('/responses/{$id}','ApprovalController@responses')->name('approval.responses');
    Route::post('/approve/{$id}', 'ApprovalController@approve')->name('approval.approve');
    Route::post('/reject/{$id}', 'ApprovalController@reject')->name('approval.reject');
});
