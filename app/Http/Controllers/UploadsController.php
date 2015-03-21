<?php namespace App\Http\Controllers;

use App\Upload;
use App\Http\Requests;
use App\Http\Controllers\Controller;

use Storage;
use Request;
use Queue;
use File;

class UploadsController extends Controller {

	public function index(){
        return 'lol';
    }

    /**
     * Store a newly created upload in storage.
     *
     * @return Response
     */
    public function store()
    {
        if (Request::hasFile('file')) {


            $file = Request::file('file');

            $acceptedMimetypes = array('application/zip', 'application/rar', 'application/x-zip-compressed', 'multipart/x-zip', 'application/x-compressed', 'application/octet-stream', 'application/x-rar-compressed', 'compressed/rar', 'application/x-rar');
            $acceptedExtensionTypes = array('zip', 'rar', 'cbz', 'cbr');

            if (in_array($file->getMimeType(), $acceptedMimetypes) && in_array($file->getClientOriginalExtension(), $acceptedExtensionTypes)) {//Make sure we're only accepting CBAs
                $upload = new Upload;
                $upload->file_original_name = $file->getClientOriginalName();
                $upload->file_size = $file->getSize();
                $newFileNameWithNoExtension = str_random(40);
                $upload->file_upload_name = $newFileName = $newFileNameWithNoExtension . '.' . $file->getClientOriginalExtension();
                $upload->user_id = "1";//Auth::user()->id;
                if (Request::get('match_data')) {
                    $upload->match_data = Request::get('match_data');
                }
                $upload->save();

                $tempPath = $file->getRealPath();
                $fileHash = hash_file('md5', $tempPath);

                Storage::disk('AWS_S3_Uploads')->put($newFileName, File::get($file));
                //Storage::disk('AWS_S3_Uploads')->put();

                //Queue::push('CollectionsController', array('upload_id' => $upload->id,'user_id'=> Auth::user()->id, 'hash'=> $fileHash, 'newFileName' => $newFileName,'newFileNameNoExt' => $newFileNameWithNoExtension, 'fileExt' => $file->getClientOriginalExtension(),'originalFileName' => $file->getClientOriginalName(),'time' => time()));

            }
        }
    }
}
