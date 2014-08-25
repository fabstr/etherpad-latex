<?php

class PdfController  extends \BaseController {
	public function view($id) 
	{
		$this -> dumpFile($id, 'application/pdf; charset=utf-8');
	}

	public function download($id)
	{
		$this -> dumpFile($id, 'application/octet-stream');
	}

	private function dumpFile($id, $contenttype) 
	{
		$user = Auth::user();
		if (!$user -> hasAccessToDocument($id)) {
			return Response::json(array(
				'failure' => 'You don\'t have access to this document.'
			), 403);
		}

		$document = Document::find($id);

		$filename = $document -> documentname;
		$filepath = $document -> filepath('pdf');
		$size = filesize($filepath);

		header('Content-type: ' . $contenttype);
		header('Content-length: ' . $size);
		header('Content-disposition: filename="'.$filename.'.pdf"');
		readfile($filepath);
	}
}
