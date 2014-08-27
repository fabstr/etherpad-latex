<?php

class TemplateController extends \BaseController {

	/**
	 * List the user's templates and return them as json.
	 *
	 * @return Response
	 */
	public function index()
	{
		return Response::json(
			Template::where('user_id', '=', Auth::id()) -> get()
		);
	}


	/**
	 * Store a newly created resource in storage.
	 *
	 * @return Response
	 */
	public function store()
	{
		// get input
		$input = Input::json();
		$name = $input -> get('templatename');
		$content = $input -> get('content');
		$userid = Auth::id();

		// create the template
		Template::create(array(
			'name' => $name,
			'content' => $content,
			'user_id' => $userid
		));
	}


	/**
	 * Return the template as json.
	 *
	 * @param  int  $templateid
	 * @return Response
	 */
	public function show($templateid)
	{
		return Response::json(Template::find($templateid));
	}


	/**
	 * Update the template.
	 *
	 * The function takes a json object as input and the properties
	 * 'templatename' and 'content' must be set (or a 400 bad request is 
	 * returned).
	 *
	 * @param  int  $templateid
	 * @return Response
	 */
	public function update($templateid)
	{
		// get input
		$input = Input::json();
		$name = $input -> get('templatename');
		$content = $input -> get('content');

		if (!isset($name) || !isset($content)) {
			return Response::json(array(
				'failure' => 'templatename or content not set',
			), 400);
		}

		// get the template
		$t = Template::find($templateid);

		// update the template
		$t -> name = $name;
		$t -> content = $content;
		$t -> save();
	}


	/**
	 * Delete the template
	 *
	 * @param  int  $templateid
	 * @return Response
	 */
	public function destroy($templateid)
	{
		// get the template...
		$template = Template::find($templateid);

		// ...and delete it
		$template -> delete();
	}


}
