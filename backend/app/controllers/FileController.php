
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
			$doc -> id . '.tex',
			$doc -> id . '.toc');

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

		// log the request
		Log::info('Storing file', array(
			'document' => $doc -> id,
			'filename' => $filename
		));
	}

	public function destroy($filename) 
	{
		// get the document and the path to the file
		$doc = Document::find(Input::get('documentid'));
		$path = $doc -> absdir() . '/' . $filename;

		// delete the file
		unlink($path);

		// log
		Log::info('Deleting file', array(
			'document' => $doc -> id,
			'filename' => $filename
		));
	}

	public function rename()
	{
		// get the name of the old/new file
		$input = Input::json();
		$oldfile = $input -> get('file');
		$newfile = $input -> get('newfile');

		// check the file name is the same
		if (pathinfo($oldfile, PATHINFO_EXTENSION) != pathinfo($newfile, PATHINFO_EXTENSION)) {
			return Response::json(array(
				'failure' => 'Could not rename file, invalid new extension.'
			), 400);
		}

		// get the document and the directory
		$doc = Document::find(Input::get('documentid'));
		$dir = $doc -> absdir();

		// rename the file
		$oldfile = $dir . '/' . $oldfile;
		$newfile = $dir . '/' . $newfile;
		rename($oldfile, $newfile);

		Log::info('Renaming file', array(
			'document' => $doc -> id,
			'oldfile' => $oldfile,
			'newfile' => $newfile
		));
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
		header('Content-disposition: filename="'.$filename.'"');

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
