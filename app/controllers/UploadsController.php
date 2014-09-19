<?php

class UploadsController extends ApiController {

    /**
     *app/controllers/UploadsController.php:5
     */
    public function __construct(){
        $user = User::find(ResourceServer::getOwnerId());
        Auth::login($user);
    }
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
	 * Store a newly created upload in storage.
	 *
	 * @return Response
	 */
	public function store()
	{


        if(Input::hasFile('file')){

            $file = Input::file('file');
            $acceptedMimetypes = array ('application/zip','application/rar','application/x-zip-compressed', 'multipart/x-zip','application/x-compressed','application/octet-stream','application/x-rar-compressed','compressed/rar','application/x-rar');
            $acceptedExtensionTypes = array ('zip', 'rar', 'cbz', 'cbr');

            if(in_array($file->getMimeType(),$acceptedMimetypes ) && in_array($file->getClientOriginalExtension(),$acceptedExtensionTypes)){//Make sure we're only accepting CBAs
                $upload = new Upload;
                $upload->file_original_name = $file->getClientOriginalName();
                $upload->file_size = $file->getSize();
                $newFileNameNoExt = str_random(40);
                $upload->file_upload_name = $newFileName = $newFileNameNoExt.'.'.$file->getClientOriginalExtension();
                $upload->user_id = Auth::user()->id;
                $upload->save();

                $tempPath = $file->getRealPath();
                $fileHash = hash_file('md5', $tempPath);
                $s3 = AWS::get('s3');
                $s3->putObject(array(
                    'Bucket'     => 'comicclouduploads',
                    'Key'        => $newFileName,
                    'SourceFile' => $tempPath,
                ));

                Queue::push('CollectionsController', array('upload_id' => $upload->id,'user_id'=> Auth::user()->id, 'hash'=> $fileHash, 'newFileName' => $newFileName,'newFileNameNoExt' => $newFileNameNoExt, 'fileExt' => $file->getClientOriginalExtension(),'originalFileName' => $file->getClientOriginalName(),'time' => time()));

                return $this->respondCreated('Upload Successful');

            }else{
                return $this->respondBadRequest('Invalid File');
            }
        }else{
            return $this->respondBadRequest('No File Uploaded');
        }


        //return Auth::user();
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
