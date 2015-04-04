<?php namespace App\Http\Controllers;

use App\Http\Requests;
use App\Http\Controllers\Controller;

use Illuminate\Http\Request;

use App\Series;

class SeriesController extends ApiController {

	/**
	 * Display a listing of the resource.
	 *
	 * @return Response
	 */
	public function index()
	{
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
