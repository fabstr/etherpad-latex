<?php

class GroupController extends \BaseController {

	/**
	 * List the groups the user owns.
	 *
	 * @return A json array 
	 */
	public function index()
	{
		// get the user's id
		$user = Auth::user();

		// return a response
		return Response::json($user -> groups());
	}


	/**
	 * Show the form for creating a new resource.
	 *
	 * @return Response
	 */
	public function create()
	{
		//
	}


	/**
	 * Create a group with the user as the owner.
	 * 
	 * @param groupname The name of the group to create
	 *
	 * @return Response
	 */
	public function store()
	{
		// get the user's id and the group name
		$userid = Auth::user() -> id;
		$input = Input::json();
		$groupname = $input -> get('groupname');

		// create the group
		$group = Group::create(array(
			'user_id' => $userid,
			'groupname' => $groupname
		));

		// add the creator to the group
		$group -> addUser($userid);

		// log the request
		Log::info('Group created', array(
			'userid' => $userid,
		       	'groupname' => $groupname
		));
	}


	/**
	 * List the documents of the group and its members and get the groupname
	 * and its owner.
	 * When this function is called it should have been determined that the
	 * user has access (in or own) the group.
	 *
	 * @param  int  $groupid 
	 * @return Response
	 */
	public function show($groupid)
	{
		// get the current group
		$group = Group::find($groupid);

		// create the response
		return Response::json(array(
			'groupname' => $group -> groupname,
			'owner' => $group -> owner(),
			'members' => $group -> users(),
			'documents' => $group -> documents()
		));
	}


	/**
	 * Show the form for editing the specified resource.
	 *
	 * @param  int  $id
	 * @return Response
	 */
	public function edit($id)
	{
		//
	}


	/**
	 * Add a user to the group.
	 *
	 * @param  int  $groupid The id of the group to add the user to
	 * @param  int  $username The name of the user to add
	 * @return Response
	 */
	public function addUser($groupid)
	{
		$input = Input::json();
		$username = $input -> get('username');
		$userid = User::where('username', '=', $username) -> pluck('id');

		// get the grop
		$group = Group::find($groupid);

		// first check that the authenticated user is the owner
		if (!$group -> isOwner(Auth::user() -> id)) {
			Return Response::json(array(
				'failure' => 'Authenticated user is not owner'
			), 401);
		}

		// put the user in the group
		$group -> addUser($userid);

		// log the request
		Log::info('Adding user to group', array(
			'groupid' => $groupid,
			'userid' => $userid
		));
	}

	/**
 	 * Remove a user from the group.
	 *
	 * @param int $groupid The id of the group
	 * @param int $userid The user to remove from the group
	 */
	public function removeUser($groupid, $userid)
	{
		// get the group
		$group = Group::find($groupid);

		// check that the authenticated user is the owner
		if (!$group -> isOwner(Auth::user() -> id)) {
			Return Response::json(array(
				'failure' => 'Authenticated user is not owner'
			), 401);
		}

		// remove the user
		$group -> removeUser($userid);

		// log the request
		Log::info('Removing user from group', array(
			'groupid' => $groupid,
			'userid' => $userid
		));
	}


	/**
	 * Remove the group. The group can only be removed by the owner and must
	 * have no documents.
	 *
	 * @param  int  $id
	 * @return Response
	 */
	public function destroy($id)
	{
		// get the group and the userid
		$group = Group::find($id);
		$userid = Auth::id();

		// log the request
		Log::info('Group deletion attempt', array(
			'userid' => $userid,
			'groupid' => $id
		));

		// check the user is the owner
		if (!$group -> isOwner($userid)) {
			Log::info('Authenticated user is not owner');
			Return Response::json(array(
				'failure' => 'Authenticated user is not owner'
			), 401);
		} 
		
		// check there are no documents in the group
		else if ($group -> numberOfDocuments() != 0) {
			Log::info('Number of documents not zero');
			Return Response::json(array(
				'failure' => 'Number of documents not zero'
			), 400);
		} 

		// the user is the owner and the group is empty, remove all 
		// users and delete the group
		else {
			Log::info('Group ' . $id . ' deleted');
			$group -> delete();
		}
	}
}
