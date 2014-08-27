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



		// list the documents
		Route::get('documents', 'DocumentController@index');

		// create a document
		Route::post('documents', 'DocumentController@store');

		// delete a document
		Route::delete('documents/{documentid}', 'DocumentController@destroy');

		// change the name of a document
		Route::post('documents/{documentid}/name', 'DocumentController@changeName');

		// change the group of a document
		Route::post('documents/{documentid}/group', 'DocumentController@changeGroup');



		// list the templates of a user
		Route::get('templates', 'TemplateController@index');

		// create a template
		Route::post('templates', 'TemplateController@store');

		// the user should own the template to be able to 
		// show/delete/update a template
		Route::group(array('before' => 'ownTemplate'), function() 
		{
			// show the template
			Route::get('templates/{templateid}', 'TemplateController@show');

			// delete a template
			Route::delete('templates/{templateid}', 'TemplateController@destroy');

			// update a template
			Route::post('templates/{templateid}', 'TemplateController@update');
		});



		// list the user's groups
		Route::get('groups', 'GroupController@index');

		// create a group
		Route::post('groups', 'GroupController@store');

		// get the contents of the group $groupid
		Route::get('groups/{groupid}', 'GroupController@show');

		// add a user to the group
		Route::post('groups/{groupid}', 'GroupController@addUser');

		// remve a user from a group
		Route::delete('groups/{groupid}/{userid}', 'GroupController@removeUser');

		// remove a group
		Route::delete('groups/{groupid}', 'GroupController@destroy');



		// all file commands (and compiling) require a get/post 
		// parameter 'documentid' and that the user has access to the 
		// document in question
		Route::group(array('before' => 'hasaccess'), function() 
		{
			// to compile the document
			Route::post('documents/compile', 'DocumentController@compile');

			// list files of a document
			Route::get('files', 'FileController@index');

			// store a file
			Route::post('files', 'FileController@store');

			// download a single file
			Route::get('files/{filename}', 'FileController@download');

			// delete a file
			Route::delete('files/{filename}', 'FileController@destroy');

			// rename a file
			Route::post('files/rename', 'FileController@rename');
		});
	});
});
