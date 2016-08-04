<?php


Route::get('/login','GUIController@generateLogin')->name('login');
Route::post('/login','AuthController@authenticate');

Route::get('/register','GUIController@generateRegister')->name('register');
Route::post('/register','AuthController@create');

Route::get('/',function() {
    return view('displays.showReports'); //works for now - prefer to move to GUIController
})->name('authHome');