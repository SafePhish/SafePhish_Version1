<?php

//Authentication
Route::get('/login','GUIController@generateLogin')->name('login');
Route::post('/login','AuthController@authenticate');
Route::get('/register','GUIController@generateRegister')->name('register');
Route::post('/register','AuthController@create');
Route::get('/auth/check','AuthController@check');
Route::get('/logout','AuthController@logout')->name('logout');

//Templates
Route::get('/templates/create','GUIController@generateCreateTemplate');
Route::post('/templates/create/phish','GUIController@createNewPhishTemplate');

//Results
Route::get('/websitedata/json','DataController@postWebsiteJson');
Route::get('/emaildata/json','DataController@postEmailJson');
Route::get('/reportsdata/json','DataController@postReportsJson');
Route::get('/','GUIController@displayResults')->name('authHome');

//Errors
Route::get('/unauthorized','ErrorController@e401')->name('e401');