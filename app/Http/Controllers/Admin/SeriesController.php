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

    /**
     * Remove the specified series from storage.
     *
     * @param  int  $id
     * @return Response
     */
    public function destroy($id){//Should this be possible?? It should only delete if a suitable replacement series is available to prevent orphan comics //TODO: Test this logic
        $series = Series::find($id);

        if($series) {
            $series->delete();

            return $this->respondSuccessful('Series Deleted');
        }
        return $this->respondNotFound([[
            'title' => 'Series Not Found',
            'detail' => 'Series Not Found',
            'status' => 404,
            'code' => ''
        ]]);
    }


    /**
     * Update the specified series in storage.
     *
     * @param  int  $id
     * @return Response
     */
    public function update($id){

        $series = Series::find($id);

        if($series){

            $validator = Validator::make($data = Request::all(), [
                'series_start_year' => 'date_format:Y',
                'comic_vine_series_id' => 'numeric'
            ]);

            if ($validator->fails()) {
                $pretty_errors = array_map(function($item){
                    return [
                        'title' => 'Missing Required Field',
                        'detail' => $item,
                        'status' => 400,
                        'code' => ''
                    ];
                }, $validator->errors()->all());

                return $this->respondBadRequest($pretty_errors);
            }

            if(empty($data)) return $this->respondBadRequest([[
                'title' => 'No Data Sent',
                'detail' => 'No Data Sent',
                'status' => 400,
                'code' => ''
            ]]);

            if (isset($data['series_title'])) $series->series_title = $data['series_title'];
            if (isset($data['series_start_year'])) $series->series_start_year = $data['series_start_year'];
            if (isset($data['series_publisher'])) $series->series_publisher = $data['series_publisher'];
            if (isset($data['comic_vine_series_id'])) $series->comic_vine_series_id = $data['comic_vine_series_id'];
            $series->save();

            return $this->respondSuccessful([
                'series' => [$series]
            ]);
        }
        return $this->respondNotFound([[
            'title' => 'Series Not Found',
            'detail' => 'Series Not Found',
            'status' => 404,
            'code' => ''
        ]]);
    }



}
