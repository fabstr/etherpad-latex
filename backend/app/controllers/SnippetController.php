<?php

class SnippetController extends \BaseController {

	/**
	 * Return all the user's snippets.
	 *
	 * @return Response
	 */
	public function index()
	{
		$user = Auth::user();
		return $user -> snippets();
	}


	/**
	 * Store a snippet.
	 * The user requesting will be the owner.
	 *
	 * JSON format: {
	 * 	snippetname: string,
	 * 	content: string
	 * }
	 *
	 */
	public function store()
	{
		// get the input
		$input = Input::json();
		$name = $input -> get('snippetname');
		$content = $input -> get('content');

		// create the snippet
		$snippet = Snippet::create(array(
			'snippetname' => $name,
			'content' => $content,
			'user_id' => Auth::id()
		));

		// Log the request
		Log::info('Snippet created', array(
			'id' => $snippet -> id,
			'user_id' => Auth::id()
		));
	}


	/**
	 * Display the specified resource.
	 *
	 * @param  int  $id
	 * @return Response
	 */
	public function show($id)
	{
		//
	}


	/**
	 * Update a snippet.
	 * The name and content will be replaced with the values of the sent
	 * json object.
	 *
	 * JSON format: {
	 * 	snippetname: string,
	 * 	content: string
	 * }
	 *
	 * @param  int  $id
	 * @return Response
	 */
	public function update($snippetid)
	{
		// get the input
		$input = Input::json();
		$name = $input -> get('snippetname');
		$content = $input -> get('content');

		// get the current snippet
		$snippet = Snippet::find($snippetid);

		// update the snippet
		$snippet -> snippetname = $name;
		$snippet -> content = $content;
		$snippet -> save();
	}


	/**
	 * Remove the specified resource from storage.
	 *
	 * @param  int  $id
	 * @return Response
	 */
	public function destroy($snippetid)
	{
		// get the snippet and delete it
		$snippet = Snippet::find($snippetid);
		$snippet -> delete();
	}


}
