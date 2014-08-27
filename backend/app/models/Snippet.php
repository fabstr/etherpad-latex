<?php

class Snippet extends Eloquent {

	/**
	 * The database table used by the model.
	 *
	 * @var string
	 */
	protected $table = 'snippets';

	protected $fillable = array('user_id', 'snippetname', 'content');

	public function user()
	{
		return User::find($this -> user_id);
	}

	public function userHasAccess($userid)
	{
		return $this -> user_id == $userid;
	}
}
