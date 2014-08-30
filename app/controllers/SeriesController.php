<?php

class SeriesController extends ApiController {

	/**
	 * Display a listing of series
	 *
	 * @return Response
	 */
	public function index()
	{

        $series = Auth::user()->series()->with('comics')->get();
        if(!$series){
            return $this->respondNotFound('No Series Found');
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
	public function store()
	{
		$validator = Validator::make($data = Input::all(), Series::$rules);

		if ($validator->fails())
		{
			return Redirect::back()->withErrors($validator)->withInput();
		}

		Series::create($data);

		return Redirect::route('series.index');
	}

	/**
	 * Display the specified series.
	 *
	 * @param  int  $id
	 * @return Response
	 */
	public function show($id)
	{
        $series = Auth::user()->series()->with('comics')->find($id);

        if(!$series){
            return $this->respondNotFound('Series Not Found');
        }

        return $this->respond([
            'series' => $series
        ]);
	}

	/**
	 * Update the specified series in storage.
	 *
	 * @param  int  $id
	 * @return Response
	 */
	public function update($id)
	{
		$series = Series::findOrFail($id);

		$validator = Validator::make($data = Input::all(), Series::$rules);

		if ($validator->fails())
		{
			return Redirect::back()->withErrors($validator)->withInput();
		}

		$series->update($data);

		return Redirect::route('series.index');
	}

	/**
	 * Remove the specified series from storage.
	 *
	 * @param  int  $id
	 * @return Response
	 */
	public function destroy($id)
	{
		//Series::destroy($id);
        if(Auth::user()->series()->find($id)->delete()){
            return $this->respondSuccessful('Series Deleted');
        }
        //else response code
        //return Redirect::route('comics.index');
	}

}
