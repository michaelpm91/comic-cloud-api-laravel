<?php

class CollectionsController extends \BaseController {

	/**
	 * Display a listing of collections
	 *
	 * @return Response
	 */
	public function index()
	{
		$collections = Collection::all();

		return View::make('collections.index', compact('collections'));
	}

	/**
	 * Show the form for creating a new collection
	 *
	 * @return Response
	 */
	public function create()
	{
		return View::make('collections.create');
	}

	/**
	 * Store a newly created collection in storage.
	 *
	 * @return Response
	 */
	public function store()
	{
		$validator = Validator::make($data = Input::all(), Collection::$rules);

		if ($validator->fails())
		{
			return Redirect::back()->withErrors($validator)->withInput();
		}

		Collection::create($data);

		return Redirect::route('collections.index');
	}

	/**
	 * Display the specified collection.
	 *
	 * @param  int  $id
	 * @return Response
	 */
	public function show($id)
	{
		$collection = Collection::findOrFail($id);

		return View::make('collections.show', compact('collection'));
	}

	/**
	 * Show the form for editing the specified collection.
	 *
	 * @param  int  $id
	 * @return Response
	 */
	public function edit($id)
	{
		$collection = Collection::find($id);

		return View::make('collections.edit', compact('collection'));
	}

	/**
	 * Update the specified collection in storage.
	 *
	 * @param  int  $id
	 * @return Response
	 */
	public function update($id)
	{
		$collection = Collection::findOrFail($id);

		$validator = Validator::make($data = Input::all(), Collection::$rules);

		if ($validator->fails())
		{
			return Redirect::back()->withErrors($validator)->withInput();
		}

		$collection->update($data);

		return Redirect::route('collections.index');
	}

	/**
	 * Remove the specified collection from storage.
	 *
	 * @param  int  $id
	 * @return Response
	 */
	public function destroy($id)
	{
		Collection::destroy($id);

		return Redirect::route('collections.index');
	}

}
