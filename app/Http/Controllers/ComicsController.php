<?php namespace App\Http\Controllers;

use App\Http\Controllers\Controller;

use App\Series;
use App\Comic;

use Validator;
use Request;


class ComicsController extends ApiController {


	/**
	 * Display a listing of the resource.
	 *
	 * @return Response
	 */
	public function index(){
        $comics = $this->currentUser->comics()->with('series')->get();

        if(!$comics) return $this->respondNotFound('No Comics Found');

        return $this->respond([
            'comics' => $comics
        ]);

	}

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return Response
     */
    public function show($id)
    {
        $comic = $this->currentUser->comics()->with('series')->find($id);

        if(!$comic) return $this->respondNotFound('Comic Not Found');

        return $this->respond([
            'comic' => $comic
        ]);
    }


	/**
	 * Update the specified resource in storage.
	 *
	 * @param  int  $id
	 * @return Response
	 */
	public function update($id)
	{

        $comic = $this->currentUser->comics()->find($id);
        if($comic){
            $validator = Validator::make($data = Request::all(), [
                'comic_issue' => 'numeric'
            ]);

            if ($validator->fails()) return $this->respondBadRequest($validator->errors());

            if(isset($data['comic_issue'])) $comic->comic_issue = $data['comic_issue'];
            if(isset( $data['comic_writer'])) $comic->comic_writer = $data['comic_writer'];
            $comic->save();

            return $this->respondSuccessful('Comic Updated');

        }else{

            return $this->respondNotFound('Comic Not Found');

        }
	}

	/**
	 * Remove the specified resource from storage.
	 *
	 * @param  int  $id
	 * @return Response
	 */
	public function destroy($id)
	{
        $series_id = $this->currentUser->comics()->find($id)['series_id'];
        if($series_id) {
            $seriesCount = Series::find($series_id)->comics()->get()->count();
            if ($this->currentUser->comics()->find($id)->delete()) {
                if ($seriesCount == 0) Series::find($series_id)->delete();
                return $this->respondSuccessful('Comic Deleted');
            }
        }
        return $this->respondNotFound('No Comic Found');

	}

}
