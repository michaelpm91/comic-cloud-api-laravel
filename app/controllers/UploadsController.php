<?php

class UploadsController extends \BaseController {

	/**
	 * Display a listing of uploads
	 *
	 * @return Response
	 */
	public function index()
	{
		$uploads = Upload::all();

		return View::make('uploads.index', compact('uploads'));
	}

	/**
	 * Show the form for creating a new upload
	 *
	 * @return Response
	 */
	public function create()
	{
		return View::make('uploads.create');
	}

	/**
	 * Store a newly created upload in storage.
	 *
	 * @return Response
	 */
	public function store()
	{
		/*$validator = Validator::make($data = Input::all(), Upload::$rules);

		if ($validator->fails())
		{
			return Redirect::back()->withErrors($validator)->withInput();
		}

		Upload::create($data);

		return Redirect::route('uploads.index');*/

        $upload = new Upload;
        //dd(Input::file('thumbnail'));
        if(Input::hasFile('file')){

            $file = Input::file('file');
            //$file = $file->move(public_path() . '/images', time() . '-' . $file->getClientOriginalName());
            $upload->file_original_name = $file->getClientOriginalName();
            //$file->getClientOriginalExtension();
            $upload->file_size = $file->getSize();
            $newFileName = $upload->file_upload_name = str_random(40);
        }

        $upload->save();

        $path = $file->getRealPath();

        $s3 = AWS::get('s3');
        $s3->putObject(array(
            'Bucket'     => 'comiccloud',
            'Key'        => $newFileName,
            'SourceFile' => $path,
        ));


        return $path;
	}

	/**
	 * Display the specified upload.
	 *
	 * @param  int  $id
	 * @return Response
	 */
	public function show($id)
	{
		$upload = Upload::findOrFail($id);

		return View::make('uploads.show', compact('upload'));
	}

	/**
	 * Show the form for editing the specified upload.
	 *
	 * @param  int  $id
	 * @return Response
	 */
	public function edit($id)
	{
		$upload = Upload::find($id);

		return View::make('uploads.edit', compact('upload'));
	}

	/**
	 * Update the specified upload in storage.
	 *
	 * @param  int  $id
	 * @return Response
	 */
	public function update($id)
	{
		$upload = Upload::findOrFail($id);

		$validator = Validator::make($data = Input::all(), Upload::$rules);

		if ($validator->fails())
		{
			return Redirect::back()->withErrors($validator)->withInput();
		}

		$upload->update($data);

		return Redirect::route('uploads.index');
	}

	/**
	 * Remove the specified upload from storage.
	 *
	 * @param  int  $id
	 * @return Response
	 */
	public function destroy($id)
	{
		Upload::destroy($id);

		return Redirect::route('uploads.index');
	}

}
