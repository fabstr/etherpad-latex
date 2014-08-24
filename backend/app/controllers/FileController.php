
<?php

class FileController  extends \BaseController {
	private $filetypes = array('application/pdf', 'application/postscript', 
		'image/gif', 'image/jpeg', 'image/pjpeg', 'image/png', 
		'image/svg+xml', 'image/vnd.djvu');

	public function index($documentid)
	{
		$doc = Document::find($documentid);

		$notlisting = array('.', '..',
			$doc -> id . '.aux',
			$doc -> id . '.fdb_latexmk',
			$doc -> id . '.fls',
			$doc -> id . '.log',
			$doc -> id . '.out',
			$doc -> id . '.pdf',
			$doc -> id . '.tex');

		// remove all files in notlisting
		$files = array_diff($doc -> listFiles(), $notlisting);

		// get the values
		$files = array_values($files);

		// return the files
		return Response::json($files);
	}

	public function store($documentid)
	{
		// get the document
		$doc = Document::find($documentid);

		// check that the file is valid
		if (!$this -> validateFileType(Input::file('file') -> getRealPath())) {
			return Response::json(array(
				'failure' => 'filetype is not valid'
			), 400);
		}

		// move the uploaded file
		$filename = Input::file('file') -> getClientOriginalName();
		Input::file('file') -> move($doc -> absdir(), $filename);
	}

	public function destroy($documentid, $fileid) 
	{

	}

	public function rename($documentid)
	{

	}

	private function validateFileType($filename)
	{
		// get the mime type of the file
		$finfo = finfo_open(FILEINFO_MIME_TYPE);
		$mime = finfo_file($finfo, $filename);
		finfo_close($finfo);

		// if the mime type is in filetypes, the file is valid
		return in_array($mime, $this -> filetypes);
	}
}
