
<?php

class FileController  extends \BaseController {
	private $filetypes = array('application/pdf', 'application/postscript', 
		'image/gif', 'image/jpeg', 'image/pjpeg', 'image/png', 
		'image/svg+xml', 'image/vnd.djvu');

	public function index()
	{
		$doc = Document::find(Input::get('documentid'));

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

	public function store()
	{
		// get the document
		$doc = Document::find(Input::get('documentid'));

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

	public function destroy($fileid) 
	{

	}

	public function rename()
	{

	}

	public function download($filename)
	{
		// get the document and the path to the file
		$doc = Document::find(Input::get('documentid'));
		$path = $doc -> absdir() . '/' . $filename;

		// get mime type of the file
		$finfo = finfo_open(FILEINFO_MIME_TYPE);
		$mime = finfo_file($finfo, $path);
		finfo_close($finfo);

		// get file size 
		$size = filesize($path);

		// write headers
		header('Content-type: ' . $mime);
		header('Content-length: ' . $size);
		header('Content-dispositiono: filename="'.$filename.'"');

		// write file
		readfile($path);
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
