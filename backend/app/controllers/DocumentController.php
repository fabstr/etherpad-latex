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
}
