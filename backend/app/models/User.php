<?php
use Illuminate\Auth\UserInterface;

class User extends Eloquent implements UserInterface {

	/**
	 * The database table used by the model.
	 *
	 * @var string
	 */
	protected $table = 'users';

	/**
	 * The attributes excluded from the model's JSON form.
	 *
	 * @var array
	 */
	protected $hidden = array('password', 'remember_token', 'authorid', 'created_at', 'updated_at');

	protected $fillable = array('username', 'password');

	public function mainGroup() 
	{
		$group = Group::where('user_id', '=', $this -> id)->first();
		return $group;
	}

	public function groups()
	{
		// select distinct id as groupid, group_user.user_id as currentuserid, groupname, ethergroupname, groups.user_id, created_at, updated_at
		// from group_user 
		// left join groups on groups.id = group_user.group_id
		//
		//return Group::where('groups.user_id', '=', $this -> id) -> get();
		return DB::table('group_user')
			-> leftJoin('groups', 'groups.id', '=', 'group_user.group_id')
			-> where('group_user.user_id', '=', $this -> id)
			-> select('groups.id as id', 'group_user.user_id as currentuserid', 'groupname', 'ethergroupname', 'groups.user_id', 'created_at', 'updated_at')
			-> distinct()
			-> get();
	}

	public function documents()
	{
		// SELECT documentname AS name, documents.id AS id, 
		//        groups.ethergroupname AS ethergroupname
		// FROM users LEFT JOIN group_user 
		//                      ON users.id = group_user.user_id
		//            LEFT JOIN groups 
		//                      ON group_user.group_id = groups.id
		//            RIGHT JOIN documents 
		//                      ON documents.group_id = groups.id

		return DB::table('users')
			-> leftJoin('group_user', 'users.id', '=', 'group_user.user_id') 
			-> leftJoin('groups', 'group_user.group_id', '=', 'groups.id')
			-> rightJoin('documents', 'documents.group_id', '=', 'groups.id')
			-> select('documentname as name', 'documents.id as id', 'groups.groupname as groupname', 'groups.ethergroupname as ethergroupname', 'groups.id as groupid')
			-> where('users.id', '=', $this -> id)
			-> get();
	}

	public function hasAccessToDocument($documentid)
	{
		$documents = $this -> documents();
		foreach ($documents as $doc) {
			if ($doc -> id == $documentid) {
				return true;
			}
		}

		return false;
	}

	public function hasAccessToGroup($groupid) 
	{
		$count = DB::table('group_user')
			-> where('user_id', '=', $this -> id)
			-> where('group_id', '=', $groupid)
			-> count();
		Log::debug($count, array(
			'user' => $this -> id,
			'group' => $groupid
			));
		return $count > 0;
	}

	public static function boot()
	{
		parent::boot();

		// when the user is created, get a etherpad author id and save
		// it with the user
		static::creating(function($user) {
			try {
				$pm = new PadManager();
				$authorid = $pm -> createAuthorIfNotExistsFor(
					$user -> username);
				$user -> authorid = $authorid;
			} catch (EtherpadException $e) {
				return false;
			}
		});
	}

	public function getAuthIdentifier() 
	{
		return $this -> id;
	}

	public function getAuthPassword() 
	{
		return $this -> password;
	}

	public function getRememberToken() 
	{
		return $this -> rember_token;
	}

	public function setRememberToken($token) 
	{
		$this -> remember_token = $token;
		$this -> save();
	}

	public function getRememberTokenName()
	{
		return 'remember_token';
	}
}
