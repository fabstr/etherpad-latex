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

		// view a pdf
		Route::get('pdf/{id}.pdf', 'PdfController@view');

		// download a pdf
		Route::get('pdf/download/{id}.pdf', 'PdfController@download');



		// to compile the document
		Route::post('documents/compile', 'DocumentController@compile');

		// list the documents
		Route::get('documents', 'DocumentController@index');

		// create a document
		Route::post('documents', 'DocumentController@store');



		// list files of a document
		Route::get('files/{documentid}', 'FileController@index');

		// download a single file
		Route::get('files/{documentid}/{filename}', 'FileController@download');

		// store a file
		Route::post('files/{documentid}', 'FileController@store');

		// delete a file
		Route::delete('files/{documentid}/{fileid}', 'FileController@destroy');

		// rename a file
		Route::post('files/{documentid}/rename', 'FileController@rename');
	});
});
