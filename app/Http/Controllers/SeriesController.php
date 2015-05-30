<?php namespace App\Http\Controllers;

use App\Http\Controllers\Controller;

use Request;
use Validator;
use Input;
use Cache;

use App\Series;
use App\Comic;


use GuzzleHttp\Client as Guzzle;

class SeriesController extends ApiController {

	/**
	 * Display a listing of the resource.
	 *
	 * @return Response
	 */
	public function index(){
        $currentUser = $this->currentUser;

        $page = (Input::get('page') ? Input::get('page') : 1);

        $series = Cache::remember('_index_series_user_id_'.$currentUser['id'].'_page_'.$page, env('route_cache_time', 10080), function() use ($currentUser) {
            $seriesArray = $currentUser->series()->paginate(env('paginate_per_page'))->toArray();
            return $seriesArray;
        });

        if(!$series){
            return $this->respondNotFound('No Series Found');
        }

        $series['series'] = $series['data'];
        unset($series['data']);

        return $this->respond($series);
	}

    /**
     * Display the specified series.
     *
     * @param  int  $id
     * @return Response
     */
    public function show($id)
    {
        $currentUser = $this->currentUser;

        $series = Cache::remember('_show_series_id_'.$id.'_user_id_'.$currentUser['id'], env('route_cache_time', 10080),function() use ($currentUser, $id) {
            return $currentUser->series()->find($id);//->with('comics')->find($id);
        });

        if(!$series){
            return $this->respondNotFound('Series Not Found');
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
    public function store(){

        Validator::extend('valid_uuid', function($attribute, $value, $parameters) {
            if(preg_match("/^(\{)?[a-f\d]{8}(-[a-f\d]{4}){4}[a-f\d]{8}(?(1)\})$/i", $value)) {
                return true;
            } else {
                return false;
            }
        });

        $messages = [
            'id.valid_uuid' => 'The :attribute field is not a valid ID.',
            'comic_id.valid_uuid' => 'The :attribute field is not a valid ID.'
        ];


        $validator = Validator::make($data = Request::all(), [
            'id' => 'required|valid_uuid',
            'comic_id' => 'required|valid_uuid',
            'series_title' => 'required',
            'series_start_year' => 'date_format:Y'
        ], $messages);

        if ($validator->fails()){//TODO Finish Error Array
            $pretty_errors = array_map(function($item){
                return [
                    'id' => '',
                    'detail' => $item,
                    'status' => '',
                    'code' => '',
                    'title' => '',

                ];
            }, $validator->errors()->all());

            return $this->respondBadRequest($pretty_errors);
        }

        if(Series::find($data['id'])) return $this->respondBadRequest("Duplicate ID Error");//TODO: Better Solution please
        $comic = $this->currentUser->comics()->find($data['comic_id']);
        if($comic) {
            $old_series_id = $comic->series_id;
            //if(Series::find($data['id'])) $data['id'] = str_random(40);//TODO: Consider duplicate IDs been sent in.
            $series = new Series;
            $series->id = $data['id'];
            $series->user_id = $this->currentUser->id;
            $series->series_title = $data['series_title'];
            $series->series_start_year = (!empty($data['series_start_year']) ? $data['series_start_year'] : date('Y'));
            $series->series_publisher = (!empty($data['series_publisher']) ? $data['series_publisher'] : "Unknown");
            $series->save();

            $comic->series_id = $series->id;
            $comic->save();

            $seriesCount = Series::find($old_series_id)->comics()->get()->count();
            if ($seriesCount == 0) Series::find($old_series_id)->delete();


            Cache::forget('_index_series_user_id_'.$this->currentUser['id']);

            return $this->respondCreated('Series Created');
        }
        return $this->respondBadRequest("Invalid Comic");

    }

    /**
     * Update the specified series in storage.
     *
     * @param  int  $id
     * @return Response
     */
    public function update($id){

        $series = $this->currentUser->series()->find($id);
        if($series){

            $validator = Validator::make($data = Request::all(), [
                'series_start_year' => 'date_format:Y',
                'comic_vine_series_id' => 'numeric'
            ]);

            if ($validator->fails()) return $this->respondBadRequest($validator->errors());

            if(empty($data)) return $this->respondBadRequest("No Data Sent");

            if (isset($data['series_title'])) $series->series_title = $data['series_title'];
            if (isset($data['series_start_year'])) $series->series_start_year = $data['series_start_year'];
            if (isset($data['series_publisher'])) $series->series_publisher = $data['series_publisher'];
            if (isset($data['comic_vine_series_id'])) $series->comic_vine_series_id = $data['comic_vine_series_id'];
            $series->save();

            Cache::forget('_index_series_user_id_'.$this->currentUser['id']);
            Cache::forget('_show_series_id_'.$id.'_user_id_'.$this->currentUser['id']);

            return $this->respondSuccessful('Series Updated');
        }
        return $this->respondNotFound('No Series Found');
    }

    /**
     * Remove the specified series from storage.
     *
     * @param  int  $id
     * @return Response
     */
    public function destroy($id){
        $series = $this->currentUser->series()->find($id);
        if($series) {
            $series->delete();

            Cache::forget('_index_series_user_id_'.$this->currentUser['id']);
            Cache::forget('_show_series_id_'.$id.'_user_id_'.$this->currentUser['id']);

            return $this->respondSuccessful('Series Deleted');
        }
        return $this->respondNotFound('No Series Found');
    }

    /**
     * Query Comic Vine API
     *
     * @param $id
     * @return mixed
     */
    public function showMetaData($id){
        $series = $this->currentUser->series()->find($id);

        if($series) {

            $guzzle = New Guzzle;

            $comic_vine_api_url = 'http://www.comicvine.com/api/search/';

            $limit = 20; //max is 100
            $page = (Input::get('page') ? Input::get('page') : 1);
            $response = Cache::remember('comic_vine_series_query_'.$series->series_title.'_page_'.$page, 10, function() use($guzzle, $comic_vine_api_url, $limit, $page, $series) {//TODO:Consider Cache time
                $guzzle_response = $guzzle->get($comic_vine_api_url, [
                    'query' => [
                        'api_key' => env('comic_vine_api_key'),
                        'format' => 'json',
                        'resources' => 'volume',
                        'limit' => $limit,
                        'page' => $page,
                        'field_list' => 'name,start_year,publisher,id,image,count_of_issues',
                        'query' => $series->series_title
                    ]
                ])->getBody();
                return (json_decode($guzzle_response, true));
            });

            if($response['status_code'] != 1) {
                return $this->respondBadRequest('Comic Vine API Error');
                //TODO: Notify Admin //json_decode($response->getBody(), true)['error']
            }
            $comic_vine_query = $response['results'];

            $series_response = array_map(function($series_entry){
                return [
                    'series_title' => $series_entry['name'],
                    'series_issues' => $series_entry['count_of_issues'],
                    'series_cover_image' => $series_entry['image']['medium_url'],
                    'start_year' => $series_entry['start_year'],
                    'publisher' => $series_entry['publisher']['name'],
                    'comic_vine_series_id' => $series_entry['id']
                ];
            }, $comic_vine_query);

            return $this->respond([
                'series' => $series_response
            ]);
        }
        return $this->respondNotFound('No Series Found');

    }

    public function showRelatedComics($id){

        $currentUser = $this->currentUser;

        $page = (Input::get('page') ? Input::get('page') : 1);

        $comic = Cache::remember('_show_related_comics_user_id_'.$currentUser['id'].'_page_'.$page, env('route_cache_time', 10080), function() use ($currentUser) {
            $seriesArray = $currentUser->series()->paginate(env('paginate_per_page'))->toArray();
            return $seriesArray;
        });

        return $this->respond($comic);
        //return $currentUser->comics()->where('series_id', '=', $id)->paginate(env('paginate_per_page'))->toArray();


    }

}
