<?php

class Group extends Eloquent {

	/**
	 * The database table used by the model.
	 *
	 * @var string
	 */
	protected $table = 'groups';

	protected $fillable = array('user_id', 'groupname', 'ethergroupname');

	public function documents()
	{
		return $this -> hasMany('Document');
	}

	public function users()
	{
		return $this -> belongsToMany('User');
	}

	public static function boot()
	{
		parent::boot();

		// when the group is created, get the etherpad group name
		// and save this with the group
		static::creating(function($group) {
			try {
				$pm = new PadManager();
				$group -> ethergroupname = $pm 
					-> createGroupIfNotExistsFor(
						$group -> id);
			} catch (EtherpadException $e) {
				return false;
			}
		});
	}
}
