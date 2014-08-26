<?php

class DocumentController extends \BaseController {

	public function __construct() 
	{
		//
	}

	/**
	 * List the documents of the (logged in) user.
	 *
	 * @return Response
	 */
	public function index()
	{
		$user = Auth::user();
		$documents = $user -> documents();
		return Response::json($documents);
	}


	/**
	 * Store the new document.
	 *
	 * @return Response
	 */
	public function store()
	{
		// get the document name
		$input = Input::json();
		$documentname = $input -> get('documentname');

		// get the user and its group
		$user = Auth::user();
		$group = $user -> mainGroup();

		// create the document
		$doc = Document::create(array(
			'documentname' => $documentname,
			'group_id' => $group -> id
		));

		Log::info('Document created', array(
			'userid' => $user -> id,
			'groupid' => $group -> id,
			'documentname' => $documentname,
			'documentid' => $doc -> id
		));
	}

	public function compile()
	{
		$input = Input::json();
		$name = $input -> get('documentid');

		Log::debug('Compiling document', array('documentid' => $name));

		try {
			$lc = new LatexCompiler($name);
			$lc -> compile();
		} catch (LatexException $e) {
			return Response::json(array(
				'message' => $e -> getMessage()
			), 500);
		}
	}

	/**
	 * Change the group of the document.
	 *
	 * Since the new group will give the document a new padid we need to 
	 * compute the old padid as well as the new one, which is done with
	 * a separate function. Then a call to etherpad's movePad(oldid, newid) 
	 * is made.
	 *
	 * @param int documentid
	 */
	public function changeGroup($documentid) 
	{
		// get input: the new group id
		$input = Input::json();
		$newGroupId = $input -> get('groupid');

		// check that the user has access to the document
		$user = Auth::user();
		if (!$user -> hasAccessToDocument($documentid)) {
			return Response::json(array(
				'failure' => 'No access to document',
			), 400);
		}

		// check that the user has access to the group
		if (!$user -> hasAccessToGroup($newGroupId)) {
			return Response::json(array(
				'failure' => 'No access to group',
			), 400);
		}

		// get the current document
		$doc = Document::find($documentid);

		// get the current and new pad id
		$currentGroupId = $doc -> group_id;
		$currentPadId = $doc -> getPadId($currentGroupId);
		$newPadId = $doc -> getPadId($newGroupId);

		// in case the pad cannot be movedwe execute the changing of 
		// group within a transaction
		DB::beginTransaction();

		// change the group of the document
		$doc -> group_id = $newGroupId;
		$doc -> save();

		try {
			// create the pad manager and move the pad
			$pm = new PadManager();
			$pm -> movePad($currentPadId, $newPadId);
		} catch (EtherpadException $e) {
			// we couldn't! rollback the group change and return an
			// error message
			DB::rollback();
			return Response::json(array(
				'failure' => 'Could not move pad',
				'message' => $e -> getMessage()
			), 500);
		}

		// sucecss, commit and return
		DB::commit();
	}

	/**
         * Change the name of the document.
	 *
	 * The name can only be changed if the user has access to the document.
	 */
	public function changeName($documentid)
	{
		// get the document
		$doc = Document::find($documentid);

		// get the new name
		$input = Input::json();
		$newname = $input -> get('newname');

		// check if the user has access
		if (!Auth::user() -> hasAccessToDocument($documentid)) {
			return Response::json(array(
				'failure' => 'No access to document'
			), 400);
		}

		$doc -> documentname = $newname;
		$doc -> save();
	}
}
