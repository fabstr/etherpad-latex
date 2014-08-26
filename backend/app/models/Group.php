<?php

class Group extends Eloquent {

	/**
	 * The database table used by the model.
	 *
	 * @var string
	 */
	protected $table = 'groups';

	protected $fillable = array('user_id', 'groupname');

	public function owner()
	{
		return User::find($this -> user_id);
	}

	public function documents()
	{
		return DB::table('documents')
			-> where('group_id', '=', $this -> id)
			-> select('id', 'documentname')
			-> get();
	}

	public function users()
	{
		return DB::table('group_user')
			-> leftJoin('users', 'group_user.user_id', '=', 'users.id')
			-> where('group_user.group_id', '=', $this -> id)
			-> select('users.id', 'users.username')
			-> get();
	}


	public function numberOfDocuments()
	{
		return DB::table('documents')
			-> where('group_id', '=', $this -> id)
			-> count();
	}

	/**
	 * Check if the user is the owner of this group.
	 * @return bool
	 */
	public function isOwner($userid)
	{
		return $this -> user_id == $userid;
	}


	public function addUser($userid)
	{
		DB::table('group_user') 
			-> insert(array(
				'user_id' => $userid,
				'group_id' => $this -> id
			));
	}

	/**
 	 * Remove the user $userid from this group.
	 */
	public function removeUser($userid)
	{
		DB::table('group_user') 
			-> where('user_id', '=', $userid)
			-> delete();
	}

	public static function boot()
	{
		parent::boot();

		// When the group is created, get the etherpad group name
		// and save this with the group. As mapping for etherpad we
		// use the sha1 hash of the concatenation of the group name and 
		// the user id.
		static::creating(function($group) {
			try {
				$mapper = sha1($group -> groupname . $group -> user_id);
				$pm = new PadManager();
				$group -> ethergroupname = $pm -> createGroupIfNotExistsFor($mapper);
			} catch (EtherpadException $e) {
				return false;
			}
		});
	}
}
