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
	protected $hidden = array('password', 'remember_token');

	protected $fillable = array('username', 'password');

	public function mainGroup() 
	{
		$group = Group::where('user_id', '=', $this -> id)->first();
		return $group;
	}

	public function groups()
	{
		return $this -> belongsToMany('Group');
	}

	public function documents()
	{
		// select documentname as name, documents.id as id, 
		//        groups.ethergroupname as ethergroupname
		// from users left join group_user 
		//                      on users.id = group_user.user_id
		//            left join groups 
		//                      on group_user.group_id = groups.id
		//            right join documents 
		//                      on documents.group_id = groups.id

		return DB::table('users')
			-> leftJoin('group_user', 'users.id', '=', 'group_user.user_id') 
			-> leftJoin('groups', 'group_user.group_id', '=', 'groups.id')
			-> rightJoin('documents', 'documents.group_id', '=', 'groups.id')
			-> select('documentname as name', 'documents.id as id', 'groups.ethergroupname as ethergroupname')
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
