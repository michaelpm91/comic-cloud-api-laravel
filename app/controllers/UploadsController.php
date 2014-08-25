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

		//return View::make('uploads.index', compact('uploads'));
        //return $uploads;
        return Auth::user();
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


        $upload = new Upload;

        if(Input::hasFile('file')){

            $file = Input::file('file');
            $upload->file_original_name = $file->getClientOriginalName();
            $upload->file_size = $file->getSize();
            $upload->file_upload_name = $newFileName = str_random(40).$file->getClientOriginalExtension();
            $upload->user_id = Auth::user()->id;
            $upload->save();

            $tempPath = $file->getRealPath();
            $s3 = AWS::get('s3');
            $s3->putObject(array(
                'Bucket'     => 'comicclouduploads',
                'Key'        => $newFileName,
                'SourceFile' => $tempPath,
            ));

            Queue::push('CreateCollection', array('upload_id' => $upload->id, 'time' => time()));

        }else{
            //return failed upload error
        }


        return Auth::user();
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
