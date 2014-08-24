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

	public function ethergroup()
	{
		$group = DB::table('documents') 
			-> leftJoin('groups', 'documents.group_id', '=', 'groups.id')
			-> where('groups.id', '=', $this -> id)
			-> pluck('groups.ethergroupname');
		return $group;
	}

	public function subdir()
	{
		return $this -> id . "_" . $this -> ethergroup();
	}

	public function filepath($extension) 
	{
		return sprintf('%s/%s/%s.%s', 
			$_ENV['WORKDIR'],
			$this -> subdir(),
			$this -> id, 
			$extension);
	}
}
