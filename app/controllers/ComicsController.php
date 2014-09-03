<?php

class ComicsController extends ApiController {

	/**
	 * Display a listing of comics
	 *
	 * @return Response
	 */
	public function index()
	{
        $comics = Auth::user()->comics()->with('series')->get();
        if(!$comics){
            return $this->respondNotFound('No Comic Found');
        }

        return $this->respond([
            'comics' => $comics
        ]);
	}

	/**
	 * Display the specified comic.
	 *
	 * @param  int  $id
	 * @return Response
	 */
	public function show($id)
	{
        $comic = Auth::user()->comics()->with('series')->find($id);

        if(!$comic){
            return $this->respondNotFound('Comic Not Found');
        }

        return $this->respond([
            'comic' => $comic
        ]);
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
		//Comic::destroy($id);
        if(Auth::user()->comics()->find($id)->delete()){
            return $this->respondSuccessful('Comic Deleted');
        }
        //else response code
		//return Redirect::route('comics.index');
	}

    /**
     *
     * Get comic meta information from Comic Vine API
     * @param $id
     */
    public function getMeta($id){
        //todo-mike Implement call to Comic Vine API
        $client = new \GuzzleHttp\Client();

        $response = $client->get('');

        dd($response->getBody());
	}
}
