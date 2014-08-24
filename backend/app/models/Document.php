<?php

class Document extends Eloquent {

	/**
	 * The database table used by the model.
	 *
	 * @var string
	 */
	protected $table = 'documents';

	protected $fillable = array('group_id', 'documentname');

	public function group()
	{
		return $this -> belongsTo('Group');
	}
}
