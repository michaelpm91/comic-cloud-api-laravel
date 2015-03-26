<?php namespace App\Http\Controllers;

use App\Commands\ProcessComicBookArchive;
use App\Commands\ProcessComicBookArchiveCommand;
use App\Commands\RandomCommand;
use App\Upload;
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

    public function __construct(Upload $upload){//
        $this->upload = $upload;//For unit testing... maybe
        Auth::loginUsingId(Authorizer::getResourceOwnerId());

    }

    /**
     * @return mixed
     */
    public function index(){


        //Queue::push(new ProcessComicBookArchiveCommand(['stuff' => 'more', 'wild' => 'van']));
        //$this->dispatch(new ProcessComicBookArchiveCommand(['stuff' => 'more', 'wild' => 'van']));
        $this->dispatch(new RandomCommand());
        $uploads = Auth::user()->uploads()->get();
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
        $upload = Auth::user()->uploads()->find($id);

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
                $upload->user_id = Auth::user()->id;
                if (Request::get('match_data')) {
                    $upload->match_data = Request::get('match_data');
                }
                $upload->save();

                $tempPath = $file->getRealPath();
                $fileHash = hash_file('md5', $tempPath);

                Storage::disk('AWS_S3_Uploads')->put($newFileName, File::get($file));

                $process_info = [
                    'upload_id' => $upload->id,
                    'user_id'=> Auth::user()->id,
                    'hash'=> $fileHash,
                    'newFileName' => $newFileName,
                    'newFileNameNoExt' => $newFileNameWithNoExtension,
                    'fileExt' => $file->getClientOriginalExtension(),
                    'originalFileName' => $file->getClientOriginalName(),
                    'time' => time()
                ];
                //Queue::push(new ProcessComicBookArchive('oh hai'));
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
