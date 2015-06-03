<?php namespace App\Http\Controllers;

use App\Http\Controllers\Controller;

use App\Series;
use App\Comic;

use Validator;
use Request;
use Input;
use Cache;
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
        $currentUser = $this->currentUser;

        $page = (Input::get('page') ? Input::get('page') : 1);
        $comic_cache_key = '_index_comics_user_id_' . $currentUser['id'] . '_page_' . $page;
        $comics = Cache::remember($comic_cache_key, env('route_cache_time', 10080), function() use ($currentUser) {
            $comicsArray = $currentUser->comics()->paginate(env('paginate_per_page'))->toArray();
            return $comicsArray;
        });

        $skip_cache_count = false;

        if(!$comics['data']) {
            Cache::forget($comic_cache_key);
            $skip_cache_count = true;
        }

        $cache_key = '_user_id_'.$currentUser['id'].'_index_comics_pages';
        if(!$skip_cache_count) {
            if (!Cache::add($cache_key, $page, env('route_cache_time', 10080))) {
                $read_pages = Cache::get($cache_key);
                $read_pages_array = explode(',', $read_pages);
                if (!in_array($page, $read_pages_array)) {
                    $read_pages_array[] = $page;
                    $read_pages_string = implode(',', $read_pages_array);
                    Cache::put($cache_key, $read_pages_string, env('route_cache_time', 10080));
                }
            }
        }

        $comics['comic'] = $comics['data'];
        unset($comics['data']);

        return $this->respond($comics);

	}

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return Response
     */
    public function show($id)
    {
        $currentUser = $this->currentUser;

        $show_comic_cache_key = '_show_comic_id_' . $id . '_user_id_' . $currentUser['id'];

        $comic = Cache::remember($show_comic_cache_key, env('route_cache_time', 10080),function() use ($currentUser, $id) {
            return $currentUser->comics()->find($id);
        });

        if(!$comic){
            Cache::forget($show_comic_cache_key);
            return $this->respondNotFound([[
                'title' => 'Comic Not Found',
                'detail' => 'Comic Not Found',
                'status' => 404,
                'code' => ''
            ]]);
        }

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

            Validator::extend('valid_uuid', function($attribute, $value, $parameters) {
                if(preg_match("/^(\{)?[a-f\d]{8}(-[a-f\d]{4}){4}[a-f\d]{8}(?(1)\})$/i", $value)) {
                    return true;
                } else {
                    return false;
                }
            });

            $messages = [
                'series_id.user_series' => 'Not a valid Series ID',
                'series_id.valid_uuid' => 'The :attribute field is not a valid ID.'
            ];

            $validator = Validator::make($data = Request::all(), [
                'comic_issue' => 'numeric',
                'series_id' => 'user_series|valid_uuid',
                'comic_vine_issue_id' => 'numeric'
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

            if(empty($data)) return $this->respondBadRequest([[
                'title' => 'No Data Sent',
                'detail' => 'No Data Sent',
                'status' => 400,
                'code' => ''
            ]]);

            if(isset($data['comic_issue'])) $comic->comic_issue = $data['comic_issue'];
            if(isset($data['comic_writer'])) $comic->comic_writer = $data['comic_writer'];
            if(isset($data['series_id'])) $comic->series_id = $data['series_id'];
            if(isset($data['comic_vine_issue_id'])) $comic->comic_vine_issue_id = $data['comic_vine_issue_id'];
            $comic->save();

            $read_pages = Cache::pull('_user_id_'. $this->currentUser['id'] .'_index_comics_pages');
            if($read_pages){
                $read_pages_array = explode(',', $read_pages);
                foreach($read_pages_array as $page){
                    Cache::forget('_index_comics_user_id_'.$this->currentUser['id'].'_page_'.$page);
                }
            }

            $read_pages = Cache::pull('_user_id_'.$this->currentUser['id'].'_show_related_comics_series_id_'.$$comic->series_id.'_pages');
            if($read_pages){
                $read_pages_array = explode(',', $read_pages);
                foreach($read_pages_array as $page){
                    Cache::forget('_show_related_comics_series_id_'.$$comic->series_id.'_user_id_' . $this->currentUser['id'] . '_page_' . $page);
                }
            }

            Cache::forget('_show_comics_id_'.$id.'_user_id_'.$this->currentUser['id']);

            return $this->respondSuccessful([
               'comic' => [$comic]
            ]);

        }else{

            return $this->respondNotFound([[
                'title' => 'Comic Not Found',
                'detail' => 'Comic Not Found',
                'status' => 404,
                'code' => ''
            ]]);

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

        $comic = $this->currentUser->comics()->find($id);
        if($comic) {
            $series_id = $comic['series']['id'];
            $this->currentUser->comics()->find($id)->delete();
            $comic_count = Series::find($series_id)->comics()->get()->count();
            if($comic_count == 0) {
                Series::find($series_id)->delete();

                $read_pages = Cache::pull('_user_id_'. $this->currentUser['id'] .'_index_series_pages');
                if($read_pages){
                    $read_pages_array = explode(',', $read_pages);
                    foreach($read_pages_array as $page){
                        Cache::forget('_index_series_user_id_'.$this->currentUser['id'].'_page_'.$page);
                    }
                }

                //TODO: Write related series cache wipe.


                Cache::forget('_show_series_id_'.$series_id.'_user_id_'.$this->currentUser['id']);
            }

            $read_pages = Cache::pull('_user_id_'. $this->currentUser['id'] .'_index_comics_pages');
            if($read_pages){
                $read_pages_array = explode(',', $read_pages);
                foreach($read_pages_array as $page){
                    Cache::forget('_index_comics_user_id_'.$this->currentUser['id'].'_page_'.$page);
                }
            }

            $read_pages = Cache::pull('_user_id_'.$this->currentUser['id'].'_show_related_comics_series_id_'.$series_id.'_pages');
            if($read_pages){
                $read_pages_array = explode(',', $read_pages);
                foreach($read_pages_array as $page){
                    Cache::forget('_show_related_comics_series_id_'.$series_id.'_user_id_' . $this->currentUser['id'] . '_page_' . $page);
                }
            }
            Cache::forget('_show_comics_id_'.$id.'_user_id_'.$this->currentUser['id']);

            return $this->respondSuccessful('Comic Deleted');
        }
        return $this->respondNotFound([[
            'title' => 'Comic Not Found',
            'detail' => 'Comic Not Found',
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
        $comic = $this->currentUser->comics()->with('series')->find($id);

        if($comic) {
            if(!$comic->series->comic_vine_series_id){
                return $this->respondBadRequest([//TODO: Detailed api error response
                    'title' => 'Comic Vine API Error',
                    'detail' => 'No Comic Vine Series ID set on parent series',
                    'status' => 400,
                    'code' => ''
                ]);
            }

            $guzzle = $this->guzzle;//New Guzzle;

            $comic_vine_api_url = 'http://comicvine.com/api/issues/';

            $limit = 20; //max is 100
            $page = (int)(Input::get('page') ? Input::get('page') : 1);
            $offset = $limit * ($page ? ($page - 1) : 0);
            $issue = (Input::get('issue') ? Input::get('issue') : '');//Should use issue data from db somehow//($comic->issue ? $comic->issue : ''));
            $comic_vine_volume_id = $comic->series->comic_vine_series_id;

            $response = Cache::remember('_comic_vine_issue_query_'.$comic_vine_volume_id.'_offset_'.$offset.= ($issue ? '_issue_'.$issue : ''), 10, function() use($guzzle, $comic_vine_api_url, $limit, $offset, $issue, $comic_vine_volume_id) {//TODO:Consider Cache time
                //TODO: Support filtering and ordering
                $guzzle_response = $guzzle->get($comic_vine_api_url, [
                    'query' => [
                        'api_key' => env('comic_vine_api_key'),
                        'format' => 'json',
                        'filter' => 'volume:' . $comic_vine_volume_id .= ($issue ? ',' . 'issue_number:' . $issue : ''),
                        'limit' => $limit,
                        'offset' => $offset,
                        'field_list' => 'name,description,issue_number,volume,id,image',
                    ]
                ])->getBody();

                return (json_decode($guzzle_response, true));
            });

            if($response['status_code'] != 1) {
                return $this->respondInternalError([
                    'title' => 'Comic Vine API Error',
                    'detail' => 'Comic Vine API Error',
                    'status' => 500,
                    'code' => '',
                ]);
                //TODO: Notify Admin //json_decode($response->getBody(), true)['error']
            }
            $comic_vine_query = $response['results'];

            $last_page = ceil($response['number_of_total_results'] / $limit);
            $current_page = ($page > $last_page ? $last_page : $page);

            if($page > $last_page) {

                Cache::forget('_comic_vine_issue_query_'.$comic_vine_volume_id.'_offset_'.$offset.= ($issue ? '_issue_'.$issue : ''));
                $guzzle = New Guzzle;//New guzzle instance because of chunking bug
                $new_offset = ($current_page - 1) * $limit;
                $response = Cache::remember('_comic_vine_issue_query_' . $comic_vine_volume_id . '_offset_' . $new_offset .= ($issue ? '_issue_' . $issue : ''), 10, function () use ($guzzle, $comic_vine_api_url, $limit, $offset, $issue, $comic_vine_volume_id, $new_offset) {//TODO:Consider Cache time
                    //TODO: Support filtering and ordering
                    $guzzle_response = $guzzle->get($comic_vine_api_url, [
                        'query' => [
                            'api_key' => env('comic_vine_api_key'),
                            'format' => 'json',
                            'filter' => 'volume:' . $comic_vine_volume_id .= ($issue ? ',' . 'issue_number:' . $issue : ''),
                            'limit' => $limit,
                            'offset' => $new_offset,
                            'field_list' => 'name,description,issue_number,volume,id,image',
                        ]
                    ])->getBody();

                    return (json_decode($guzzle_response, true));
                });

                //dd($response);
                if($response['status_code'] != 1) {
                    return $this->respondInternalError([[
                        'title' => 'Comic Vine API Error',
                        'detail' => 'Comic Vine API Error',
                        'status' => 500,
                        'code' => '',
                    ]]);
                    //TODO: Notify Admin //json_decode($response->getBody(), true)['error']
                }
            }


            $issues = array_map(function($issue_entry){
                return [
                    'issue_description' => strip_tags($issue_entry['description']),
                    'issue_name' => $issue_entry['name'],
                    'issue_number' => $issue_entry['issue_number'],
                    'comic_vine_issue_id' => $issue_entry['id']
                ];
            }, $comic_vine_query);

            usort($issues, function($a, $b) {
                return $a['issue_number'] - $b['issue_number'];
            });

            $comic_meta_url = url('v'.env('APP_API_VERSION').'/comic/'. $id .'/meta?page=');


            $next_link = ($current_page + 1 >= $last_page ? null : $comic_meta_url.($current_page + 1));
            $prev_link = ($current_page - 1 <= 1 ? null : $comic_meta_url.($current_page - 1));

            $from = ($current_page - 1) * $limit + 1;

            return $this->respond([
                'total' => $response['number_of_total_results'],
                'per_page' => $response['limit'],
                'current_page' => (int)$current_page,
                'last_page' => $last_page,
                'next_page_url' => $next_link,
                'prev_page_url' => $prev_link,
                'from' => ($from < 0 ? 1 : $from),
                'to' => (($current_page - 1)  * $limit) + $response['number_of_page_results'],
                'issue' => $issues
            ]);
        }
        return $this->respondNotFound([[
            'title' => 'Comic Not Found',
            'detail' => 'Comic Not Found',
            'status' => 404,
            'code' => ''
        ]]);

    }

    public function showRelatedSeries($comic_id){//TODO: Finish relationship
        //TODO: User checks?
        $currentUser = $this->currentUser;

        $page = (Input::get('page') ? Input::get('page') : 1);

        //Query comic -> Get series id -> get series data
        $comic_related_series_cache_key = '_show_related_series_comic_id_'.$comic_id.'_user_id_' . $currentUser['id'] . '_page_' . $page;

        $series = Cache::remember($comic_related_series_cache_key, env('route_cache_time', 10080), function() use ($currentUser, $comic_id) {
            $seriesArray = $currentUser->series()->comics()->paginate(env('paginate_per_page'))->toArray();
            return $seriesArray;
        });

        $skip_cache_count = false;

        /*if(!$series['data']) {
            Cache::forget($comic_related_series_cache_key);
            $skip_cache_count = true;
        }

        $cache_key = '_user_id_'.$currentUser['id'].'_show_related_comics_series_id_'.$series_id.'_pages';
        if(!$skip_cache_count) {
            if (!Cache::add($cache_key, $page, env('route_cache_time', 10080))) {
                $read_pages = Cache::get($cache_key);
                $read_pages_array = explode(',', $read_pages);
                if (!in_array($page, $read_pages_array)) {
                    $read_pages_array[] = $page;
                    $read_pages_string = implode(',', $read_pages_array);
                    Cache::put($cache_key, $read_pages_string, env('route_cache_time', 10080));
                }
            }
        }

        $comic['comic'] = $comic['data'];
        unset($comic['data']);


        return $this->respond($comic);*/


    }

}
