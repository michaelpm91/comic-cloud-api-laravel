<?php namespace App\Http\Controllers\Admin;

use App\Http\Controllers\ApiController;
use App\AdminUpload;

use LucaDegasperi\OAuth2Server\Authorizer;

use DB;


class UploadsController extends ApiController {

    /*public function __construct(Authorizer $authorizer){
        parent::__construct($authorizer);
        //$currentUser = $this->currentUser;
        if($this->currentUserType != 'admin') {
            return $this->respondUnauthorised();
        }
    }*/

    /**
     * @return mixed
     */
    public function index(){

        $uploads = AdminUpload::with('user')->paginate(env('paginate_per_page'))->toArray();
        //$uploads = DB::table('uploads')->paginate(env('paginate_per_page'))->toArray();

        $uploads['upload'] = $uploads['data'];
        unset($uploads['data']);

        return $this->respond($uploads);
    }


}
