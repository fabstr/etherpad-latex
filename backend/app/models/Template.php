<?php

class Template extends Eloquent {

	/**
	 * The database table used by the model.
	 *
	 * @var string
	 */
	protected $table = 'templates';

	/**
 	 * The fields that can be entered with Template::create()
	 *
	 * @var array
	 */
	protected $fillable = array('name', 'user_id', 'content');

	/**
	 * The fields that are not shown when converting the model to json.
	 *
	 * @var array
	 */
	protected $hidden = array('created_at', 'updated_at');

	/**
 	 * Return the template's owner.
	 */
	public function user()
	{
		return $this -> belongsTo('User');
	}
}
