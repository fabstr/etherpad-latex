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
		$doc = new Document(array(
			'documentname' => $documentname,
			'group_id' => $group -> id
		));

		// put the document in the group
		$group -> documents() -> save($doc);
	}

	public function compile()
	{
		$input = Input::json();
		$name = $input -> get('documentid');

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
