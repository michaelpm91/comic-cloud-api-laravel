<?php

class ComicsController extends ApiController {

    /**
     * Login in resource owner
     */
    public function __construct(){
        $user = User::find(ResourceServer::getOwnerId());
        Auth::login($user);
    }
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
		/*$comic = Comic::findOrFail($id);

		$validator = Validator::make($data = Input::all(), Comic::$rules);

		if ($validator->fails())
		{
			return Redirect::back()->withErrors($validator)->withInput();
		}

		$comic->update($data);

		return Redirect::route('comics.index');*/

        $comic = Auth::user()->comics()->find($id);
        if($comic){

            $validator = Validator::make($data = Input::all(), Comic::$rules);

            if ($validator->fails())
            {
                //return Redirect::back()->withErrors($validator)->withInput();
                //todo-mike: Create error stuff
            }

            //$series->update($data);
            /*$series->series_title = $data['title'];
            $series->series_start_year = $data['start_year'];
            $series->series_publisher = $data['publisher'];*/
            $comic->comic_issue = $data['issue'];
            $comic->comic_writer = $data['writer'];
            $comic->save();
            return $this->respondSuccessful('Comic Updated');
        }
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
        $series_id = Auth::user()->comics()->find($id)['series_id'];
        $seriesCount = Series::find($series_id)->comics()->get()->count();
        if(Auth::user()->comics()->find($id)->delete()){
            if($seriesCount == 0)  Series::find($series_id)->delete();
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
