<?php namespace App\Http\Controllers;

use App\Http\Controllers\Controller;

use Request;
use Validator;
use Input;
use Cache;

use App\Models\Series;
use App\Models\Comic;


use GuzzleHttp\Client as Guzzle;

use App\Http\Controllers\ApiController;


class SeriesController extends ApiController {

	/**
	 * Display a listing of the resource.
	 *
	 * @return Response
	 */
	public function index(){
        $currentUser = $this->currentUser;

        $page = (Input::get('page') ? Input::get('page') : 1);
        $series = $currentUser->series()->paginate(env('paginate_per_page'))->toArray();

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

        $series = $currentUser->series()->find($id);

        if(!$series){
            return $this->respondNotFound([[
                'title' => 'Series Not Found',
                'detail' => 'Series Not Found',
                'status' => 404,
                'code' => ''
            ]]);
        }

        return $this->respond([
            'series' => [$series]
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

        if ($validator->fails()){
            $pretty_errors = array_map(function($item){
                return [
                    'title' => 'Missing Required Field',
                    'detail' => $item,
                    'status' => 400,
                    'code' => ''
                ];
            }, $validator->errors()->all());

            return $this->respondBadRequest($pretty_errors);
        }

        //if(Series::find($data['id'])) return $this->respondBadRequest("Duplicate ID Error");//TODO: Duplicate Client ID solution
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

            return $this->respondCreated([
                'series' => [$series]
            ]);
        }
        return $this->respondBadRequest([[
            'title' => 'Invalid Comic ID',
            'detail' => 'Invalid Comic ID',
            'status' => 400,
            'code' => ''
        ]]);

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

            if ($validator->fails()) {
                $pretty_errors = array_map(function($item){
                    return [
                        'title' => 'Missing Required Field',
                        'detail' => $item,
                        'status' => 400,
                        'code' => ''
                    ];
                }, $validator->errors()->all());

                return $this->respondBadRequest($pretty_errors);
            }

            if(empty($data)) return $this->respondBadRequest([[
                'title' => 'No Data Sent',
                'detail' => 'No Data Sent',
                'status' => 400,
                'code' => ''
            ]]);

            if (isset($data['series_title'])) $series->series_title = $data['series_title'];
            if (isset($data['series_start_year'])) $series->series_start_year = $data['series_start_year'];
            if (isset($data['series_publisher'])) $series->series_publisher = $data['series_publisher'];
            if (isset($data['comic_vine_series_id'])) $series->comic_vine_series_id = $data['comic_vine_series_id'];
            $series->save();

            return $this->respondSuccessful([
                'series' => [$series]
            ]);
        }
        return $this->respondNotFound([[
            'title' => 'Series Not Found',
            'detail' => 'Series Not Found',
            'status' => 404,
            'code' => ''
        ]]);
    }

    /**
     * Remove the specified series from storage.
     *
     * @param  int  $id
     * @return Response
     */
    public function destroy($id){//Should this be possible?? It should only delete if a suitable replacement series is available to prevent orphan comics //TODO: Test this logic
        $series = $this->currentUser->series()->find($id);
        if($series) {
            $series->delete();

            return $this->respondSuccessful('Series Deleted');
        }
        return $this->respondNotFound([[
            'title' => 'Series Not Found',
            'detail' => 'Series Not Found',
            'status' => 404,
            'code' => ''
        ]]);
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
            $response = Cache::remember('_comic_vine_series_query_'.$series->series_title.'_page_'.$page, 10, function() use($guzzle, $comic_vine_api_url, $limit, $page, $series) {//TODO:Consider Cache time
                //TODO: Support filtering and ordering
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
                return $this->respondBadRequest([[
                    'title' => 'Comic Vine API Error',
                    'detail' => 'Comic Vine API Error',
                    'status' => 500,
                    'code' => '',
                ]]);
                //TODO: Notify Admin //json_decode($response->getBody(), true)['error']
            }


            $last_page = ceil($response['number_of_total_results'] / $limit);
            $current_page = ($page > $last_page ? $last_page : $page);

            if($page > $last_page) {

                Cache::forget('_comic_vine_series_query_'.$series->series_title.'_page_'.$page);
                $guzzle = New Guzzle;
                $response = Cache::remember('_comic_vine_series_query_'.$series->series_title.'_page_'.$last_page, 10, function() use($guzzle, $comic_vine_api_url, $limit, $last_page, $series) {//TODO:Consider Cache time
                    //TODO: Support filtering and ordering
                    $guzzle_response = $guzzle->get($comic_vine_api_url, [
                        'query' => [
                            'api_key' => env('comic_vine_api_key'),
                            'format' => 'json',
                            'resources' => 'volume',
                            'limit' => $limit,
                            'page' => $last_page,
                            'field_list' => 'name,start_year,publisher,id,image,count_of_issues',
                            'query' => $series->series_title
                        ]
                    ])->getBody();
                    return (json_decode($guzzle_response, true));
                });

                if($response['status_code'] != 1) {
                    return $this->respondBadRequest([[
                        'title' => 'Comic Vine API Error',
                        'detail' => 'Comic Vine API Error',
                        'status' => 500,
                        'code' => '',
                    ]]);
                    //TODO: Notify Admin //json_decode($response->getBody(), true)['error']
                }

            }

            $comic_vine_query = $response['results'];


            $series_response = array_map(function($series_entry){
                return [
                    'series_title' => $series_entry['name'],
                    'series_issues' => $series_entry['count_of_issues'],
                    'series_cover_image' => $series_entry['image']['medium_url'],
                    'start_year' => (int)$series_entry['start_year'],
                    'publisher' => $series_entry['publisher']['name'],
                    'comic_vine_series_id' => $series_entry['id']
                ];
            }, $comic_vine_query);


            $series_meta_url = url('v'.env('APP_API_VERSION').'/series/'. $id .'/meta?page=');


            $next_link = ($current_page + 1 >= $last_page ? null : $series_meta_url.($current_page + 1));
            $prev_link = ($current_page - 1 <= 1 ? null : $series_meta_url.($current_page - 1));

            $from = ($current_page - 1) * $limit + 1;

            return $this->respond([
                'total' =>  $response['number_of_total_results'],
                'per_page' => $response['limit'],
                'current_page' => (int)$current_page,
                'last_page' => $last_page,
                'next_page_url' => $next_link,
                'prev_page_url' => $prev_link,
                'from' => ($from < 0 ? 1 : $from),
                'to' => (($current_page - 1)  * $limit) + $response['number_of_page_results'],
                'series' => $series_response
            ]);
        }
        return $this->respondNotFound([[
            'title' => 'Series Not Found',
            'detail' => 'Series Not Found',
            'status' => 404,
            'code' => ''
        ]]);

    }

    public function showRelatedComics($series_id){

        $currentUser = $this->currentUser;

        $page = (Input::get('page') ? Input::get('page') : 1);//TODO: Find out if this is actually needed

        $comic = $currentUser->comics()->where('series_id', '=', $series_id)->paginate(env('paginate_per_page'))->toArray();

        $comic['comic'] = $comic['data'];
        unset($comic['data']);

        return $this->respond($comic);

    }

}
