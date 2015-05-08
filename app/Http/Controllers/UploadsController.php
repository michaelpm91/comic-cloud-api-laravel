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
use Validator;
use Cache;

class UploadsController extends ApiController {

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

        $currentUser = $this->currentUser;

        $uploads = Cache::rememberForever('_index_upload_user_id_'.$currentUser['id'], function() use ($currentUser) {
            return $currentUser->uploads()->get();
        });

        if(!$uploads){
            return $this->respondNotFound('No Uploads Found');
        }

        return $this->respond([
            'uploads' => $uploads
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
        $currentUser = $this->currentUser;
        $upload = $this->currentUser->uploads()->find($id);

        $upload = Cache::rememberForever('_show_'.$id.'_upload_user_id_'.$currentUser['id'], function() use ($currentUser, $id) {
            return $currentUser->uploads()->find($id);
        });

        if(!$upload){
            return $this->respondNotFound('No Upload Found');
        }

        return $this->respond([
            'upload' => $upload
        ]);
    }

    /**
     * Store a newly created upload in storage.
     *
     * @return Response
     */
    public function store(){//TODO: Multiple Uploads in one request.

        Validator::extend('valid_cba', function($attribute, $value, $parameters) {
            $acceptedMimetypes = array ('application/zip','application/rar','application/x-zip-compressed', 'multipart/x-zip','application/x-compressed','application/octet-stream','application/x-rar-compressed','compressed/rar','application/x-rar');
            $acceptedExtensionTypes = array ('zip', 'rar', 'cbz', 'cbr');
            if(in_array($value->getMimeType(),$acceptedMimetypes ) && in_array($value->getClientOriginalExtension(),$acceptedExtensionTypes)) {
                return true;
            }else{
                return false;
            }
        });

        $messages = [
            'file.valid_cba' => 'Not a valid File',
        ];

        $validator = Validator::make(Request::all(), [
            'file' => 'required|valid_cba|between:1,150000',
            "exists" => 'required|boolean',
            "series_id" => 'required|alpha_num|min:40|max:40',
            "comic_id" => 'required|alpha_num|min:40|max:40',
            "series_title" => 'required',
            "series_start_year" => 'required|numeric',
            "comic_issue" => 'required|numeric',
        ], $messages);

        if ($validator->fails()){
            return $this->respondBadRequest($validator->errors());
        }

        $file = Request::file('file');

        $upload = new Upload;
        $upload->file_original_name = $file->getClientOriginalName();
        $upload->file_size = $file->getSize();
        $newFileNameWithNoExtension = $upload->file_random_upload_id = str_random(40);
        $upload->file_upload_name = $newFileName = $newFileNameWithNoExtension . '.' . $file->getClientOriginalExtension();
        $upload->file_original_file_type = $file->getClientOriginalExtension();
        $upload->user_id = $this->currentUser->id;
        $upload->match_data = json_encode(Request::except('file'));
        $upload->save();

        $tempPath = $file->getRealPath();
        $fileHash = hash_file('md5', $tempPath);

        Storage::disk(env('user_uploads', 'local_user_uploads'))->put($newFileName, File::get($file));

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

        Cache::forget('_index_upload_user_id_'.$this->currentUser['id']);

        return $this->respondCreated('Upload Successful');

    }


}
