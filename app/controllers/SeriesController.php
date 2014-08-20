<?php

class SeriesController extends \BaseController {

	/**
	 * Display a listing of series
	 *
	 * @return Response
	 */
	public function index()
	{
		$series = Series::all();

		return View::make('series.index', compact('series'));
	}

	/**
	 * Show the form for creating a new series
	 *
	 * @return Response
	 */
	public function create()
	{
		return View::make('series.create');
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
		$series = Series::findOrFail($id);

		return View::make('series.show', compact('series'));
	}

	/**
	 * Show the form for editing the specified series.
	 *
	 * @param  int  $id
	 * @return Response
	 */
	public function edit($id)
	{
		$series = Series::find($id);

		return View::make('series.edit', compact('series'));
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
		Series::destroy($id);

		return Redirect::route('series.index');
	}

}
