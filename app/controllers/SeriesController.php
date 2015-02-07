<?php

class SeriesController extends ApiController {

    /**
     * Login in resource owner
     */
    public function __construct(){
        $user = Auth::loginUsingId(Authorizer::getResourceOwnerId());
    }

    /**
     * Display a listing of series
     *
     * @return Response
     */
	public function index()
	{

        //$series = User::find($this->user_id)->series()->with('comics')->get();
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
        //$series = User::find($this->user_id)->series()->with('comics')->find($id);
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
		/*$series = Series::findOrFail($id);

		$validator = Validator::make($data = Input::all(), Series::$rules);

		if ($validator->fails())
		{
			return Redirect::back()->withErrors($validator)->withInput();
		}

		$series->update($data);

		return Redirect::route('series.index');*/
        $series = Auth::user()->series()->find($id);
        if($series){

            //$series->series_title = $data;

            $validator = Validator::make($data = Input::all(), Series::$rules);

            if ($validator->fails())
            {
                //return Redirect::back()->withErrors($validator)->withInput();
                //todo-mike: Create error stuff
            }

		    //$series->update($data);
            $series->series_title = $data['title'];
            $series->series_start_year = $data['start_year'];
            $series->series_publisher = $data['publisher'];
            $series->save();
            return $this->respondSuccessful('Series Updated');
        }
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
