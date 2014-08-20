<?php

class ComicsController extends \BaseController {

	/**
	 * Display a listing of comics
	 *
	 * @return Response
	 */
	public function index()
	{
		$comics = Comic::all();

		return View::make('comics.index', compact('comics'));
	}

	/**
	 * Show the form for creating a new comic
	 *
	 * @return Response
	 */
	public function create()
	{
		return View::make('comics.create');
	}

	/**
	 * Store a newly created comic in storage.
	 *
	 * @return Response
	 */
	public function store()
	{
		$validator = Validator::make($data = Input::all(), Comic::$rules);

		if ($validator->fails())
		{
			return Redirect::back()->withErrors($validator)->withInput();
		}

		Comic::create($data);

		return Redirect::route('comics.index');
	}

	/**
	 * Display the specified comic.
	 *
	 * @param  int  $id
	 * @return Response
	 */
	public function show($id)
	{
		$comic = Comic::findOrFail($id);

		return View::make('comics.show', compact('comic'));
	}

	/**
	 * Show the form for editing the specified comic.
	 *
	 * @param  int  $id
	 * @return Response
	 */
	public function edit($id)
	{
		$comic = Comic::find($id);

		return View::make('comics.edit', compact('comic'));
	}

	/**
	 * Update the specified comic in storage.
	 *
	 * @param  int  $id
	 * @return Response
	 */
	public function update($id)
	{
		$comic = Comic::findOrFail($id);

		$validator = Validator::make($data = Input::all(), Comic::$rules);

		if ($validator->fails())
		{
			return Redirect::back()->withErrors($validator)->withInput();
		}

		$comic->update($data);

		return Redirect::route('comics.index');
	}

	/**
	 * Remove the specified comic from storage.
	 *
	 * @param  int  $id
	 * @return Response
	 */
	public function destroy($id)
	{
		Comic::destroy($id);

		return Redirect::route('comics.index');
	}

}
