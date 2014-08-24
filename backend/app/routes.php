<?php

/*
|--------------------------------------------------------------------------
| Application Routes
|--------------------------------------------------------------------------
|
| Here is where you can register all of the routes for an application.
| It's a breeze. Simply tell Laravel the URIs it should respond to
| and give it the Closure to execute when that URI is requested.
|
 */

Route::group(array('prefix' => 'latex/rest'), function() 
{
	// to log in a user
	Route::post('login', 'LoginController@login');

	// to log out a user
	Route::get('logout', 'LoginController@logout');

	// to check if a user is logged in
	Route::get('isloggedin', 'LoginController@isLoggedIn');

	// a user can be created without loggin in
	Route::post('create', 'UserController@store');

	// other function requre login
	Route::group(array('before' => 'auth'), function() 
	{
		// to authenticate the user for its groups and set the sessionid
		// cookie
		Route::post('user/createsessions', 'UserController@createsessions');

		// to compile the document
		Route::post('documents/compile', 'DocumentController@compile');

		Route::resource('user', 'UserController');
		Route::resource('groups', 'GroupController');
		Route::resource('documents', 'DocumentController');
	});
});
