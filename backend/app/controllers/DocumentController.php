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
		// begin the transaction
		DB::beginTransaction();

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

		// get the template id (if 0 no template should be used)
		$templateid = $input -> get('templateid');
		if ($templateid != 0) {
			// a template should be used
			if ($user -> ownTemplate($templateid)) {
				// the user owns this template, set the text
				// of the document to the content of the 
				// template

				// get the template
				$t = Template::find($templateid);

				// get the document's pad id
				$padid = $doc -> getPadId();

				try {
					$pm = new PadManager();
					$pm -> setText($padid, $t -> content);
				} catch (EtherpadException $e) {
					DB::rollback();
					Log::info('Could not create document because of etherpad exception', array(
						'message' => $e -> getMessage()
					));
				}
			}
		}

		// all successfull, commit 
		DB::commit();

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
			), 401);
		}

		$doc -> documentname = $newname;
		$doc -> save();
	}

	/**
 	 * Remove the document from the database and etherpad.
	 *
	 * First check that the user has access to the document.
	 *
	 * Three things happen:
	 * 1. The document is removed from etherpad
	 * 2. The files are deleted from disk
	 * 3. The document is removed from the database
	 *
	 * @param int documentid
	 */
	public function destroy($documentid)
	{
		if (!Auth::user() -> hasAccessToDocument($documentid)) {
			return Response::json(array(
				'failure' => 'No access to document'
			), 401);
		}

		// get the document
		$doc = Document::find($documentid);

		// 1. remove the document from etherpad
		try {
			$pm = new PadManager();
			$pm -> deletePad($doc -> getPadId());
		} catch (EtherpadException $e) {
			if ($e -> code == 1) {
				// the pad does not exist, do nothing
			} else {
				// log the failure
				Log::info('Could not delete pad', array(
					'padid' => $doc -> getPadId(),
					'message' => $e -> getMessage(),
				));

				// some other error, return failure
				return Response::json(array(
					'failure' => 'EtherpadException',
					'message' => $e -> getMessage()
				));
			}
		}

		// 2. remove the files from disk: list the files in the 
		// directory, remove then and finally the directory
		$dir = $doc -> absdir();
		if (!file_exists($dir)) {
			// the directory (and therefor the files) does not
			// exist, do nothing
		} else {
			// delete the files in the directory
			foreach (scandir($dir) as $file) {
				if ($file != "." && $file != "..") {
					unlink($dir . "/" . $file);
				}
			}
			// delete the directory
			rmdir($dir);
		}

		// 3. remove the document from the database
		$doc -> delete();

		Log::info('Document was removed', array(
			'documentid' => $documentid,
			'user' => Auth::id()
		));
	}
}
