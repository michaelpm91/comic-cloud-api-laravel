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
use Input;

use Illuminate\Pagination\LengthAwarePaginator;

use Rhumsaa\Uuid\Uuid;

class UploadsController extends ApiController {

    /**
     * @return mixed
     */
    public function index(){

        $currentUser = $this->currentUser;

        $page = (Input::get('page') ? Input::get('page'): 1);
        $upload_cache_key = '_index_uploads_user_id_'.$currentUser['id'].'_page_'.$page;
        $uploads = Cache::remember($upload_cache_key, env('route_cache_time', 10080), function() use ($currentUser) {
            $uploadsArray = $currentUser->uploads()->paginate(env('paginate_per_page'))->toArray();
            return $uploadsArray;
        });
        $skip_cache_count = false;

        if(!$uploads['data']) {
            Cache::forget($upload_cache_key);
            $skip_cache_count = true;
        }

        $cache_key = '_user_id_'.$currentUser['id'].'_index_uploads_pages';
        if(!$skip_cache_count) {
            if (!Cache::add($cache_key, $page, env('route_cache_time', 10080))) {
                $read_pages = Cache::get($cache_key);
                $read_pages_array = explode(',', $read_pages);
                if (!in_array($page, $read_pages_array)) {
                    $read_pages_array[] = $page;
                    $read_pages_string = implode(',', $read_pages_array);
                    Cache::put($cache_key, $read_pages_string, env('route_cache_time', 10080));
                }
            }
        }

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

        $upload = Cache::remember('_show_uploads_id_'.$id.'_user_id_'.$currentUser['id'], env('route_cache_time', 10080),function() use ($currentUser, $id) {
            return $currentUser->uploads()->find($id);
        });

        if(!$upload){
            Cache::forget('_show_uploads_id_'.$id.'_user_id_'.$currentUser['id']);
            return $this->respondNotFound([
                'id' => '',
                'detail' => 'Not Found',
                'status' => 404,
                'code' => '',
                'title' => '',
            ]);
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

        $messages = [
            'file.valid_cba' => 'Not a valid File.',
            'series_id.valid_uuid' => 'The :attribute field is not a valid ID.',
            'comic_id.valid_uuid' => 'The :attribute field is not a valid ID.'
        ];

        $validator = Validator::make(Request::all(), [
            'file' => 'required|valid_cba|between:1,150000',
            'exists' => 'required|boolean',
            'series_id' => 'required|valid_uuid',
            'comic_id' => 'required|valid_uuid',
            'series_title' => 'required',
            'series_start_year' => 'required|numeric',
            'comic_issue' => 'required|numeric',
        ], $messages);

        if ($validator->fails()){//TODO Finish Error Array
            $pretty_errors = array_map(function($item){
                return [
                    'id' => '',
                    'detail' => $item,
                    'status' => '',
                    'code' => '',
                    'title' => '',

                ];
            }, $validator->errors()->all());

            return $this->respondBadRequest($pretty_errors);
        }

        $file = Request::file('file');

        $upload = new Upload;
        $upload->file_original_name = $file->getClientOriginalName();
        $upload->file_size = $file->getSize();
        $newFileNameWithNoExtension = $upload->file_random_upload_id = Uuid::uuid4();
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
            'user_id'=> $currentUser['id'],
            'hash'=> $fileHash,
            'newFileName' => $newFileName,
            'newFileNameNoExt' => $newFileNameWithNoExtension,
            'fileExt' => $file->getClientOriginalExtension(),
            'originalFileName' => $file->getClientOriginalName(),
            'time' => time()
        ];

        Queue::push(new ProcessComicBookArchiveCommand($process_info));

        $read_pages = Cache::pull('_user_id_'. $currentUser['id'] .'_index_uploads_pages');
        if($read_pages){
            $read_pages_array = explode(',', $read_pages);
            foreach($read_pages_array as $page){
                Cache::forget('_index_uploads_user_id_'.$currentUser['id'].'_page_'.$page);
            }
        }

        return $this->respondCreated('Upload Successful');

    }


}
