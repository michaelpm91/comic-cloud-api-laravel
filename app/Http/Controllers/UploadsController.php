<?php namespace App\Http\Controllers;

use App\Commands\ProcessComicBookArchive;
use App\Commands\ProcessComicBookArchiveCommand;
use App\Upload;
use App\User;
use App\ComicBookArchive;
use App\Http\Requests;
use App\Http\Controllers\Controller;

use Storage;
use Request;
use Queue;
use File;
use Auth;
use Validator;
use Input;

use Illuminate\Pagination\LengthAwarePaginator;

use Rhumsaa\Uuid\Uuid;

class UploadsController extends ApiController {

    /**
     * @return mixed
     */
    public function index(){

        $currentUser = $this->currentUser;

        $page = (Input::get('page') ? Input::get('page'): 1);//TODO: Find out if this is actually needed
        $uploads = $currentUser->uploads()->paginate(env('paginate_per_page'))->toArray();

        $uploads['upload'] = $uploads['data'];
        unset($uploads['data']);

        return $this->respond($uploads);
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

        $upload = $currentUser->uploads()->find($id);

        if(!$upload){
            return $this->respondNotFound([
                'title' => 'Upload Not Found',
                'detail' => 'Upload Not Found',
                'status' => 404,
                'code' => ''
            ]);
        }

        return $this->respond([
            'upload' => [$upload]
        ]);
    }

    /**
     * Store a newly created upload in storage.
     *
     * @return Response
     */
    public function store(){//TODO: Multiple Uploads in one request.

        $currentUser = $this->currentUser;

        Validator::extend('valid_cba', function($attribute, $value, $parameters) {
            $acceptedMimetypes = array ('application/zip','application/rar','application/x-zip-compressed', 'multipart/x-zip','application/x-compressed','application/octet-stream','application/x-rar-compressed','compressed/rar','application/x-rar');
            $acceptedExtensionTypes = array ('zip', 'rar', 'cbz', 'cbr');
            if(in_array($value->getMimeType(),$acceptedMimetypes ) && in_array($value->getClientOriginalExtension(),$acceptedExtensionTypes)) {
                return true;
            }else{
                return false;
            }
        });

        Validator::extend('valid_uuid', function($attribute, $value, $parameters) {
            if(preg_match("/^(\{)?[a-f\d]{8}(-[a-f\d]{4}){4}[a-f\d]{8}(?(1)\})$/i", $value)) {
                return true;
            } else {
                return false;
            }
        });

        Validator::extend('user_comics', function($attribute, $value, $parameters) {
            if($this->currentUser->comics()->find($value)){
                return false;
            }else{
                return true;
            }
        });

        $messages = [
            'file.valid_cba' => 'Not a valid File.',
            'comic_id.user_comics' => 'Not a valid Comic ID',
            'series_id.valid_uuid' => 'The :attribute field is not a valid ID.',
            'comic_id.valid_uuid' => 'The :attribute field is not a valid ID.'
        ];

        $validator = Validator::make(Request::all(), [
            'file' => 'required|valid_cba|between:1,150000',
            'exists' => 'required|boolean',
            'series_id' => 'required|valid_uuid',
            'comic_id' => 'required|valid_uuid|user_comics',
            'series_title' => 'required',
            'series_start_year' => 'required|numeric',
            'comic_issue' => 'required|numeric',
        ], $messages);

        if ($validator->fails()){
            $pretty_errors = array_map(function($item){
                return [
                    'title' => 'Missing Required Field Or Incorrectly Formatted Data',
                    'detail' => $item,
                    'status' => 400,
                    'code' => ''
                ];
            }, $validator->errors()->all());

            return $this->respondBadRequest($pretty_errors);
        }

        $file = Request::file('file');
        $fileHash = hash_file('md5', $file->getRealPath());


        $upload = new Upload;
        $upload->file_original_name = $file->getClientOriginalName();
        $upload->file_size = $file->getSize();
        $newFileNameWithNoExtension = $upload->file_random_upload_id = Uuid::uuid4()->toString();
        $upload->file_upload_name = $newFileName = $newFileNameWithNoExtension . '.' . $file->getClientOriginalExtension();
        $upload->file_original_file_type = $file->getClientOriginalExtension();
        $upload->user_id = $this->currentUser->id;
        $upload->match_data = json_encode(Request::except('file'));
        $upload->save();



        $cba = ComicBookArchive::where('comic_book_archive_hash', '=', $fileHash)->first();



        if(!$cba){//Upload not found so send file to S3
            Storage::disk(env('user_uploads', 'local_user_uploads'))->put($newFileName, File::get($file));
            //create cba
            $cba = $this->createComicBookArchive($upload->id, $fileHash);
        }

        //check if series exists, if not create one



        /*$process_info = [
            'upload_id' => $upload->id,
            'user_id'=> $currentUser['id'],
            'hash'=> $fileHash,
            'newFileName' => $newFileName,
            'newFileNameNoExt' => $newFileNameWithNoExtension,
            'fileExt' => $file->getClientOriginalExtension(),
            'originalFileName' => $file->getClientOriginalName(),
            'time' => time()
        ];

        Queue::push(new ProcessComicBookArchiveCommand($process_info));*/

        //TODO: Create Comics Structure On Upload



        return $this->respondCreated([
            'upload' => [$upload]
        ]);

    }

    /**
     * @return ComicBookArchive
     */
    private function createComicBookArchive($upload_id, $cba_hash){
        $process_info = $this->message;

        $cba = new ComicBookArchive();
        $cba->upload_id = $upload_id;
        $cba->comic_book_archive_hash = $cba_hash;
        $cba->comic_book_archive_status = 0;
        $cba->save();

        return $cba;
    }
    /**
     * @param $match_data
     * @return mixed
     */
    private function getSeriesInfo($match_data){
        $series = User::find($this->user_id)->first()->series()->find($match_data['series_id']);
        if($match_data['exists'] || !$match_data['exists'] && !$series){//create
            $series = $this->createSeries($match_data);
        }
        return $series->id;
    }

    /**
     * @param $match_data
     * @return Series
     */
    private function createSeries($match_data){
        $series = new Series;
        $newSeriesID = $match_data['series_id'];//TODO:Reconsider Client ID Generation //(Series::find($match_data['series_id']) ? str_random(40) : $match_data['series_id']);//If ID generated by client already exists, generate a new one
        $series->id = $newSeriesID;
        $series->series_title = $match_data['series_title'];
        $series->series_start_year = $match_data['series_start_year'];
        $series->series_publisher = 'Unknown';
        $series->user_id = $this->user_id;
        $series->save();
        return $series;
    }


}
