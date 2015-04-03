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

class UploadsController extends ApiController {

    protected $upload;
    //protected $user;
    //public $user;

    /*public function __construct(Upload $upload){//
        //$this->upload = $upload;//For unit testing... maybe
        parent::__construct();

    }*/

    /**
     * @return mixed
     */
    public function index(){

        $uploads = $this->currentUser->uploads()->get();
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
        $upload = $this->currentUser->uploads()->find($id);

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
                $newFileNameWithNoExtension = $upload->file_random_upload_id = str_random(40);
                $upload->file_upload_name = $newFileName = $newFileNameWithNoExtension . '.' . $file->getClientOriginalExtension();
                $upload->file_original_file_type = $file->getClientOriginalExtension();
                $upload->user_id = $this->currentUser->id;

                $match_data = Request::get('match_data');

                if ($match_data) { //TODO: This validation should be a Laravel validator
                    $required_keys = ["exists", "series_id", "comic_id", "series_title", "series_start_year", "comic_issue"];
                    $match_data_array = json_decode($match_data, true);
                    if(!is_array($match_data_array)) return $this->respondBadRequest('Invalid Match Data');
                    if (count(array_diff($required_keys, array_keys($match_data_array))) != 0) return $this->respondBadRequest('Invalid Match Data');
                    $upload->match_data = $match_data;

                }else{
                    return $this->respondBadRequest('No Match Data');
                }
                $upload->save();

                $tempPath = $file->getRealPath();
                $fileHash = hash_file('md5', $tempPath);

                Storage::disk(env('user_uploads'))->put($newFileName, File::get($file));

                $process_info = [
                    'upload_id' => $upload->id,
                    'user_id'=> $this->currentUser->id,
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
