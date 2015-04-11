<?php namespace App\Http\Controllers;

use App\Http\Controllers\Controller;

use App\Series;
use App\Comic;

use Validator;
use Request;
use Input;
use GuzzleHttp\Client as Guzzle;

use LucaDegasperi\OAuth2Server\Authorizer;


class ComicsController extends ApiController {

    protected $guzzle;

    public function __construct(Guzzle $guzzle, Authorizer $authorizer)
    {
        parent::__construct($authorizer);
        $this->guzzle = $guzzle;
    }

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
            Validator::extend('user_series', function($attribute, $value, $parameters) {
                if($this->currentUser->series()->with('comics')->find($value)){
                    return true;
                }else{
                    return false;
                }
            });

            $messages = [
                'series_id.user_series' => 'Not a valid Series ID',
            ];

            $validator = Validator::make($data = Request::all(), [
                'comic_issue' => 'numeric',
                'series_id' => 'user_series|alpha_num|min:40|max:40'
            ], $messages);

            if ($validator->fails()) return $this->respondBadRequest($validator->errors());

            if(empty($data)) return $this->respondBadRequest("No Data Sent");

            if(isset($data['comic_issue'])) $comic->comic_issue = $data['comic_issue'];
            if(isset($data['comic_writer'])) $comic->comic_writer = $data['comic_writer'];
            if(isset($data['series_id'])) $comic->series_id = $data['series_id'];
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
        /*$series_id = $this->currentUser->comics()->find($id)['series_id'];
        if($series_id) {
            $seriesCount = Series::find($series_id)->comics()->get()->count();
            if ($this->currentUser->comics()->find($id)->delete()) {
                if ($seriesCount == 0) Series::find($series_id)->delete();
                return $this->respondSuccessful('Comic Deleted');
            }
        }*/
        $comic = $this->currentUser->comics()->find($id);
        if($comic) {
            $series_id = $comic['series']['id'];
            $this->currentUser->comics()->find($id)->delete();
            $comic_count = Series::find($series_id)->comics()->get()->count();
            if($comic_count == 0) Series::find($series_id)->delete();
            return $this->respondSuccessful('Comic Deleted');
        }
        return $this->respondNotFound('No Comic Found');

	}

    /**
     * Query Comic Vine API
     *
     * @param $id
     * @param $page
     * @return mixed
     */
    public function getMeta($id){
        $comic = $this->currentUser->comics()->with('series')->find($id);

        if($comic) {

            $guzzle = $this->guzzle;//New Guzzle;

            $comic_vine_api_url = 'http://www.comicvine.com/api/search/';
            //	echo $url = 'http://comicvine.com/api/issues/?api_key='.$apikey.'&format=json&field_list=name,description,issue_number,volume,id,image&filter=volume:'.$volumeid.',issue_number:'.$issue;
            //OR //	$url= 'http://comicvine.com/api/issues/?api_key='.$apikey.'&format=json&limit=100&field_list=name,description,issue_number,volume,id,image&filter=volume:'.$volumeID.'&offset='.(100*($page-1));


            $limit = 20; //max is 100
            $page = (Input::get('page') ? Input::get('page') : 1);

            $response = $guzzle->get($comic_vine_api_url, [
                'query' => [
                    'api_key' => env('comic_vine_api_key'),
                    'format' => 'json',
                    'resources' => 'volume',
                    'limit' => $limit,
                    'page' => $page,
                    'field_list' => 'name,start_year,publisher,id,image,count_of_issues',
                    'query' => $comic->series->series_title
                ]
            ]);
            if(json_decode($response->getBody(), true)['status_code'] != 1) {
                return $this->respondBadRequest('Comic Vine API Error');
                //TODO: Notify Admin //json_decode($response->getBody(), true)['error']
            }
            $comic_vine_query = json_decode($response->getBody(), true)['results'];

            $series = array_map(function($series_entry){
                return [
                    'series_title' => $series_entry['name'],
                    'series_issues' => $series_entry['count_of_issues'],
                    'series_cover_image' => $series_entry['image']['medium_url'],
                    'start_year' => $series_entry['start_year'],
                    'publisher' => $series_entry['publisher']['name']
                ];
            }, $comic_vine_query);

            return $this->respond([
                'series' => $series
            ]);
        }
        return $this->respondNotFound('No Comic Found');

    }

}
