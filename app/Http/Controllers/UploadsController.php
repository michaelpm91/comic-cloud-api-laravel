<?php namespace App\Http\Controllers;

use App\Commands\ProcessComicBookArchive;
use App\Commands\ProcessComicBookArchiveCommand;
use App\Upload;
use App\User;
use App\Http\Requests;
use App\Http\Controllers\Controller;

use Storage;
use Request;
use Queue;
use File;
use Auth;
use Authorizer;

class UploadsController extends ApiController {

    protected $upload;
    //protected $user;
    public $user;

    public function __construct(Upload $upload){//
        //$this->upload = $upload;//For unit testing... maybe
        //if(Authorizer::getResourceOwnerId()) $this->user = User::findOrFail(Authorizer::getResourceOwnerId());

    }

    /**
     * @return mixed
     */
    public function index(){
        $this->user = User::findOrFail(Authorizer::getResourceOwnerId());
        dd($this->user);

        $uploads = $this->user->uploads()->get();
        if(!$uploads){
            return $this->respondNotFound('No Uploads Found');
        }

        return $this->respond([
            'Uploads' => $uploads
        ]);
    }

    /**
     * Display the specified upload.
     *
     * @param  int  $id
     * @return Response
     */
    public function show($id)
    {
        $upload = $this->user->uploads()->find($id);

        if(!$upload){
            return $this->respondNotFound('No Upload Found');
        }

        return $this->respond([
            'Upload' => $upload
        ]);
    }

    /**
     * Store a newly created upload in storage.
     *
     * @return Response
     */
    public function store(){//TODO: Multiple Uploads in one request.

        if (Request::hasFile('file')) {


            $file = Request::file('file');
            //dd($file);

            $acceptedMimetypes = array('application/zip', 'application/rar', 'application/x-zip-compressed', 'multipart/x-zip', 'application/x-compressed', 'application/octet-stream', 'application/x-rar-compressed', 'compressed/rar', 'application/x-rar');
            $acceptedExtensionTypes = array('zip', 'rar', 'cbz', 'cbr');

            if (in_array($file->getMimeType(), $acceptedMimetypes) && in_array($file->getClientOriginalExtension(), $acceptedExtensionTypes)) {//Make sure we're only accepting CBAs
                $upload = new Upload;
                $upload->file_original_name = $file->getClientOriginalName();
                $upload->file_size = $file->getSize();
                $newFileNameWithNoExtension = str_random(40);
                $upload->file_upload_name = $newFileName = $newFileNameWithNoExtension . '.' . $file->getClientOriginalExtension();
                $upload->user_id = $this->user->id;
                if (Request::get('match_data')) {
                    $upload->match_data = Request::get('match_data');
                }
                $upload->save();

                $tempPath = $file->getRealPath();
                $fileHash = hash_file('md5', $tempPath);

                Storage::disk(env('user_uploads'))->put($newFileName, File::get($file));

                $process_info = [
                    'upload_id' => $upload->id,
                    'user_id'=> $this->user->id,
                    'hash'=> $fileHash,
                    'newFileName' => $newFileName,
                    'newFileNameNoExt' => $newFileNameWithNoExtension,
                    'fileExt' => $file->getClientOriginalExtension(),
                    'originalFileName' => $file->getClientOriginalName(),
                    'time' => time()
                ];

                Queue::push(new ProcessComicBookArchiveCommand($process_info));

                return $this->respondCreated('Upload Successful');

            } else {
                return $this->respondBadRequest('Invalid File');
            }
        }else{
            return $this->respondBadRequest('No File Uploaded');
        }
    }


}
