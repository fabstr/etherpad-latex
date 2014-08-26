<?php

class LoginController  extends \BaseController {
	public function login() 
	{
		// get the json input and decode
		$input = Input::json();

		// get the username and password then create an array of the 
		// data to use in Auth::attempt(). Let Auth::attempt() handle
		// the hashing of the password.
		$credentials = array(
			'username' => $input->get('username'),
			'password' => $input->get('password')
		);


		// try to authenticate the user and remember the login if 
		// successfull
		if (Auth::attempt($credentials , true)) {
			// if the data is correct, return ok
			return Response::json(array(
				'status' => 'ok'
			));
		} 

		// log the failure
		Log::info('Login failure', array(
			'username' => $input -> get('username')
		));

		// else return failure
		return Response::json(array(
			'status' => 'failure'
		), 404);
	}

	public function logout()
	{
		// logout the user
		Auth::logout();
	}

	public function isLoggedIn()
	{
		if (Auth::check() == false) {
			return Response::make("", 403);
		}
	}
}
