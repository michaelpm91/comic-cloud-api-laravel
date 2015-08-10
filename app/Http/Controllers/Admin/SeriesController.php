<?php namespace App\Http\Controllers\Admin;

use App\Http\Controllers\ApiController;

class SeriesController extends ApiController {


    /**
     * @return mixed
     */
    public function index(){

        $uploads = AdminUpload::with('user')->paginate(env('paginate_per_page'))->toArray();

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

        $upload = AdminUpload::with('user')->find($id);


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

}
