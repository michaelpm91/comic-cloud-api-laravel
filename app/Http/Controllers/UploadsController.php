<?php namespace App\Http\Controllers;

use App\Upload;
use App\User;
use App\Series;
use App\Comic;
use App\ComicBookArchive;
use App\Http\Requests;

use Storage;
use Request;
use Queue;
use File;
use Auth;
use Validator;
use Input;

use AWS;

use Illuminate\Pagination\LengthAwarePaginator;

use Rhumsaa\Uuid\Uuid;

use Illuminate\Http\Response;


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
        $match_data = Request::except('file');

        //Write Upload to DB
        $upload = new Upload;
        $upload->file_original_name = $file->getClientOriginalName();
        $upload->file_size = $file->getSize();
        $newFileNameWithNoExtension = $upload->file_random_upload_id = Uuid::uuid4()->toString();
        $upload->file_upload_name = $newFileName = $newFileNameWithNoExtension . '.' . $file->getClientOriginalExtension();
        $upload->file_original_file_type = $file->getClientOriginalExtension();
        $upload->user_id = $this->currentUser->id;
        $upload->match_data = json_encode($match_data);
        $upload->save();

        //Check if CBA exists
        $cba = ComicBookArchive::where('comic_book_archive_hash', '=', $fileHash)->first();
        $process_cba = false;
        //If not write an entry for one to the DB and send the file to S3
        if(!$cba){//Upload not found so send file to S3
            Storage::disk(env('user_uploads', 'local_user_uploads'))->put($newFileName, File::get($file));//TODO: Make sure right AWS S3 ACL is used in production
            //Storage::disk(env('user_uploads', 'local_user_uploads'))->getDriver()->getAdapter


            //create cba
            $cba = $this->createComicBookArchive($upload->id, $fileHash);
            $process_cba = true;
        }

        //check if series exists, if not create one
        $series = User::find($this->currentUser->id)->first()->series()->find($match_data['series_id']);

        //dd($series);

        if(!$series){//create
            $series = $this->createSeries($match_data);
        }

        //$series->id;

        $comic_info = [
            'comic_issue' => $match_data['comic_issue'],
            'comic_id' => $match_data['comic_id'],
            'series_id' => $series->id,
            'comic_writer' => 'Unknown',
            'comic_book_archive_id' => $cba->id
        ];

        $comic = $this->createComic($comic_info);
        $uploadLocation = "https://s3"/*.env('AWS_REGION', 'us-east-1')*/.".amazonaws.com/".env('AWS_S3_Uploads')."/".$newFileName; //TODO: This ideally needs to something returned from Laravel's upload
        //dd($uploadLocation);

        //invoke lambda
        if($process_cba) {
            $s3 = AWS::get('s3');
            $s3TempLink = $s3->getObjectUrl(env('user_uploads', 'local_user_uploads'), $newFileName, '+10 minutes');

            $lambda = AWS::get('Lambda');
            $lambda->invokeAsync([
                'FunctionName' => env('LAMBDA_FUNCTION_NAME'),
                'InvokeArgs' => json_encode([
                    "api_version" => url('v'.env('APP_API_VERSION')),
                    "environment" => env('APP_ENV'),
                    "fileLocation" => $s3TempLink,
                    "cba_id" => $cba->id
                ]),
            ]);

        }



        return $this->respondCreated([
            'upload' => [$upload]
        ]);

    }

    public function showS3Signature(){


        /*$algorithm = "AWS4-HMAC-SHA256";
        $service = "s3";
        $date = gmdate('Ymd\THis\Z');
        $shortDate = gmdate('Ymd');
        $requestType = "aws4_request";
        $expires = '600'; // 24 Hours
        $successStatus = '201';

        $scope = [
            env('AWS_ACCESS_KEY_ID'),
            $shortDate,
            env('AWS_REGION'),
            $service,
            $requestType
        ];
        $credentials = implode('/', $scope);

        $policy = [
            'expiration' => gmdate('Y-m-d\TG:i:s\Z', strtotime('+10 Minutes')),
            'conditions' => [
                ['bucket' => env('AWS_S3_Uploads')],
                ['acl' => 'private'],
                ['starts-with', '$key', ''],
                ['starts-with', '$Content-Type', ''],
                ['success_action_status' => $successStatus],
                ['x-amz-credential' => $credentials],
                ['x-amz-algorithm' => $algorithm],
                ['x-amz-date' => $date],
                ['x-amz-expires' => $expires],
            ]
        ];
        $base64Policy = base64_encode(json_encode($policy));

        // Signing Keys
        $dateKey = hash_hmac('sha256', $shortDate, 'AWS4' .env('AWS_SECRET_ACCESS_KEY'), true);
        $dateRegionKey = hash_hmac('sha256', env('AWS_REGION'), $dateKey, true);
        $dateRegionServiceKey = hash_hmac('sha256', $service, $dateRegionKey, true);
        $signingKey = hash_hmac('sha256', $requestType, $dateRegionServiceKey, true);

        // Signature
        $signature = hash_hmac('sha256', $base64Policy, $signingKey);
        return [
            'policy' => $base64Policy,
            'x-amz-algorithm' => 'AWS4-HMAC-SHA256',
            'x-amz-credentials' => $credentials,
            'x-amz-date' => $date,
            'signature' => $signature
        ];*/

        $aws_date = gmdate('Ymd\THis\Z');
        $aws_request_type = "aws4_request";
        $aws_short_date = gmdate('Ymd');
        $aws_service_type = "s3";
        $aws_scope = [
            env('AWS_ACCESS_KEY_ID'),
            $aws_short_date,
            env('AWS_REGION'),
            $aws_service_type,
            $aws_request_type
        ];
        $aws_credentials = implode('/', $aws_scope);
        $amz_success_status = 201;
        $amz_algorithm = "AWS4-HMAC-SHA256";

        $policy = [
            'expiration' => gmdate('Y-m-d\TG:i:s\Z', strtotime('+10 Minutes')),
            'conditions' => [
                ['bucket' => env('AWS_S3_Uploads')],
                ['acl' => 'private'],
                ['starts-with', '$key', ''],
                ['starts-with', '$Content-Type', ''],
                ['success_action_status' => $amz_success_status],
                ['x-amz-credential' => $aws_credentials],
                ['x-amz-algorithm' => $amz_algorithm],
                ['x-amz-date' => $aws_date],
            ]
        ];
        $base64Policy = base64_encode(json_encode($policy));

        // Signing Keys
        $dateKey = hash_hmac('sha256', $aws_short_date , 'AWS4' .env('AWS_SECRET_ACCESS_KEY'), true);
        $dateRegionKey = hash_hmac('sha256', env('AWS_REGION'), $dateKey, true);
        $dateRegionServiceKey = hash_hmac('sha256', $aws_service_type, $dateRegionKey, true);
        $signingKey = hash_hmac('sha256', $aws_request_type, $dateRegionServiceKey, true);

        // Signature
        $signature = hash_hmac('sha256', $base64Policy, $signingKey);

        return [
            "policy" => $base64Policy,
            "X-Amz-Algorithm" => $amz_algorithm,
            "X-Amz-Date" => $aws_date,
            "X-Amz-Credential" => $aws_credentials,
            "signature" => $signature
        ];
    }

    /**
     * @return ComicBookArchive
     */
    private function createComicBookArchive($upload_id, $cba_hash)
    {
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
     * @return Series
     */
    private function createSeries($match_data){
        $series = new Series;
        $newSeriesID = $match_data['series_id'];
        $series->id = $newSeriesID;
        $series->series_title = $match_data['series_title'];
        $series->series_start_year = $match_data['series_start_year'];
        $series->series_publisher = 'Unknown';
        $series->user_id = $this->currentUser->id;
        $series->save();
        return $series;
    }

    /**
     * @param $comic_info
     * @return Comic
     */
    private function createComic($comic_info){

        $cba = ComicBookArchive::find($comic_info['comic_book_archive_id']);

        $newComicID = $comic_info['comic_id'];

        $comic = new Comic;
        $comic->id = $newComicID;
        $comic->comic_issue = $comic_info['comic_issue'];
        $comic->comic_writer = $comic_info['comic_writer'];
        $comic->comic_book_archive_contents = (($cba->comic_book_archive_contents ? $cba->comic_book_archive_contents : ''));
        $comic->user_id = $this->currentUser->id;
        $comic->series_id = $comic_info['series_id'];
        $comic->comic_book_archive_id = $cba->id;
        $comic->save();

        return $comic;
    }


}
