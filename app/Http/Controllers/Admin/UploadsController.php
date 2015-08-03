<?php namespace App\Http\Controllers\Admin;

use App\Http\Controllers\ApiController;
use App\Upload;

use DB;


class UploadsController extends ApiController {

    /**
     * @return mixed
     */
    public function index(){

        $uploads = Upload::with('user')->paginate(env('paginate_per_page'))->toArray();
        //$uploads = DB::table('uploads')->paginate(env('paginate_per_page'))->toArray();

        $uploads['upload'] = $uploads['data'];
        unset($uploads['data']);

        return $this->respond($uploads);
    }


}