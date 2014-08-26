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
			-> where('documents.id', '=', $this -> id)
			-> pluck('groups.ethergroupname');
		return $group;
	}

	public function subdir()
	{
		return $this -> id . "_" . $this -> ethergroup();
	}

	public function absdir()
	{
		return $_ENV['WORKDIR'] . '/' . $this -> subdir();
	}

	public function filepath($extension) 
	{
		return sprintf('%s/%s/%s.%s', 
			$_ENV['WORKDIR'],
			$this -> subdir(),
			$this -> id, 
			$extension);
	}

	public function listFiles()
	{
		$files = scandir($this -> absdir());
		return $files;
	}

	/**                                                                                                                       
	 * Compute the padid of this document and the given group id.                                                        
	 *                                                                                                                 
	 * The padid is the concatenation of the group's ethergroupname, a '$'                                                    
	 * and the docucument's id. If $groupid is false or not set, ethergroup()
	 * will be used.
	 *                                                                                                                       
	 * @param int groupid                                                                                                   
	 * @return The padid                                                                                                   
	 */                                                                                                                   
	public function getPadId($groupid = false)
	{                                                                                                                  
		$ethergroupname = "";

		if ($groupid) {
			// get the group and the etherpad name
			$group = Group::find($groupid);                                                                                  
			$ethergroupname = $group -> ethergroupname;                                                                    
		} else {
			$ethergroupname = $this -> ethergroup();
		}

		// make the concatenation and return the pad id                                                               
		$padid = $ethergroupname . '$' . $this -> id;                                                                
		return $padid;                                                                                              
	}
}
