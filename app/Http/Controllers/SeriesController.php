<?php namespace App\Http\Controllers;

use App\Http\Controllers\Controller;

use Request;
use Validator;

use App\Series;
use App\Comic;

class SeriesController extends ApiController {

	/**
	 * Display a listing of the resource.
	 *
	 * @return Response
	 */
	public function index(){
        $series = $this->currentUser->series()->with('comics')->get();
        if(!$series){
            return $this->respondNotFound('No Series Found');
        }

        return $this->respond([
            'series' => $series
        ]);
	}

    /**
     * Display the specified series.
     *
     * @param  int  $id
     * @return Response
     */
    public function show($id)
    {
        $series = $this->currentUser->series()->with('comics')->find($id);

        if(!$series){
            return $this->respondNotFound('Series Not Found');
        }

        return $this->respond([
            'series' => $series
        ]);
    }


    /**
     * Store a newly created series in storage.
     *
     * @return Response
     */
    public function store(){
        $validator = Validator::make($data = Request::all(), [
            'id' => 'required|alpha_num|min:40|max:40',
            'comic_id' => 'required|alpha_num|min:40|max:40',
            'series_title' => 'required',
            'series_start_year' => 'date_format:Y'
        ]);

        if ($validator->fails())
        {
            return $this->respondBadRequest($validator->errors());
        }
        if(Series::find($data['id'])) return $this->respondBadRequest("Duplicate ID Error");//TODO: Better Solution please
        $comic = $this->currentUser->comics()->find($data['comic_id']);
        if($comic) {
            $old_series_id = $comic->series_id;
            //if(Series::find($data['id'])) $data['id'] = str_random(40);//TODO: Consider duplicate IDs been sent in.
            $series = new Series;
            $series->id = $data['id'];
            $series->user_id = $this->currentUser->id;
            $series->series_title = $data['series_title'];
            $series->series_start_year = (!empty($data['series_start_year']) ? $data['series_start_year'] : date('Y'));
            $series->series_publisher = (!empty($data['series_publisher']) ? $data['series_publisher'] : "Unknown");
            $series->save();

            $comic->series_id = $series->id;
            $comic->save();

            $seriesCount = Series::find($old_series_id)->comics()->get()->count();
            if ($seriesCount == 0) Series::find($old_series_id)->delete();

            return $this->respondCreated('Series Created');
        }
        return $this->respondBadRequest("Invalid Comic");

    }

    /**
     * Update the specified series in storage.
     *
     * @param  int  $id
     * @return Response
     */
    public function update($id)
    {

        $series = $this->currentUser->series()->find($id);
        if($series){

            $validator = Validator::make($data = Request::all(), [
                'series_start_year' => 'date_format:Y'
            ]);

            if ($validator->fails()) return $this->respondBadRequest($validator->errors());

            if(empty($data)) return $this->respondBadRequest("No Data Sent");

            if (isset($data['series_title'])) $series->series_title = $data['series_title'];
            if (isset($data['series_start_year'])) $series->series_start_year = $data['series_start_year'];
            if (isset($data['series_publisher'])) $series->series_publisher = $data['series_publisher'];
            $series->save();

            return $this->respondSuccessful('Series Updated');
        }
        return $this->respondNotFound('No Series Found');
    }

    /**
     * Remove the specified series from storage.
     *
     * @param  int  $id
     * @return Response
     */
    public function destroy($id)
    {
        $series = $this->currentUser->series()->find($id);
        if($series) {
            $series->delete();
            return $this->respondSuccessful('Series Deleted');
        }
        return $this->respondNotFound('No Series Found');
    }

}
