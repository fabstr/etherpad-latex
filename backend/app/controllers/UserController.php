<?php

class UserController extends \BaseController {

	/**
	 * Store a newly created resource in storage.
	 *
	 * @return Response
	 */
	public function store()
	{
		// begin the transaction
		// if the etherpad group cannot be created, we use this to abort
		// user-creation
		DB::beginTransaction();

		// get the username and the password, hashing the password
		$username = Input::get('username');
		$password = Hash::make(Input::get('password'));

		// add the user user to the database and get the new user
		$user = new User();
		$user = User::create(array(
			'username' => $username,
			'password' => $password
		));


		// create a group for the user
		$group = Group::create(array(
			'groupname' => $username,
			'user_id' => $user -> id,
		));

		// put the user in the group
		$group -> addUser($user -> id);

		// commit the user to the database
		DB::commit();

		// login the user
		Auth::login($user);

		Log::info('User created', array(
			'username' => $username,
			'userid' => $user -> id
		));

		// return the user 
		return Response::json(array(
			'status' => 'ok',
			'user' => $user,
		));
	}

	// create a session between the and its groups by setting the sessionID
	// cookie.
	public function createSessions()
	{
		$input = Input::json();
		$group = $input -> get('group');
		$groupfound = false; // set to true when the group is found 
		                     // amongst the user's groups

		$validuntil = time() + 3600*24;

		$user = Auth::user();
		$sessionIDs = "";
		$pm = new PadManager();
		foreach ($user -> groups() as $g) {
			if ($g -> ethergroupname == $group) {
				$groupfound = true;
			}

			$sessionid = $pm -> createSession($g -> ethergroupname,
			       	$user -> authorid, $validuntil);
			$sessionIDs .= $sessionid . ",";
		}

		$sessionIDs = trim($sessionIDs, ",");

		if ($groupfound == false) {
			return Response::json(array(
				'failure' => 'group not found',
				'group' => $group,
				'groups' => $user -> groups
			), 404);
		}

		Log::info('Session created', array(
			'userid' => $user -> id,
			'sessionID' => $sessionIDs
		));

		setcookie("sessionID", $sessionIDs, $validuntil, "/");
		return Response::json(array(
			'sessionIDs' => $sessionIDs
		));
	}
}
