<?php namespace App\Http\Controllers\Admin;

use App\Http\Controllers\ApiController;
use App\Models\Admin\Series;


class SeriesController extends ApiController {


    /**
     * @return mixed
     */
    public function index(){

        $series = Series::paginate(env('paginate_per_page'))->toArray();

        $series['series'] = $series['data'];
        unset($series['data']);

        return $this->respond($series);
    }

    /**
     * Display the specified upload.
     *
     * @param  int  $id
     * @return Response
     */
    public function show($id)
    {

        $series = Series::find($id);


        if(!$series){
            return $this->respondNotFound([
                'title' => 'Series Not Found',
                'detail' => 'Series Not Found',
                'status' => 404,
                'code' => ''
            ]);
        }

        return $this->respond([
            'series' => [$series]
        ]);
    }

}
