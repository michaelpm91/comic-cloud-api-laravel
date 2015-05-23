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

        $comics = Cache::remember('_index_comics_user_id_'.$currentUser['id'], env('route_cache_time', 10080), function() use ($currentUser) {
            return $currentUser->comics()->with('series')->get();
        });

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
        $currentUser = $this->currentUser;

        $comic = Cache::remember('_show_comic_id_'.$id.'_user_id_'.$currentUser['id'], env('route_cache_time', 10080),function() use ($currentUser, $id) {
            return $currentUser->comics()->with('series')->find($id);
        });

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

            if(empty($data)) return $this->respondBadRequest("No Data Sent");

            if(isset($data['comic_issue'])) $comic->comic_issue = $data['comic_issue'];
            if(isset($data['comic_writer'])) $comic->comic_writer = $data['comic_writer'];
            if(isset($data['series_id'])) $comic->series_id = $data['series_id'];
            if(isset($data['comic_vine_issue_id'])) $comic->comic_vine_issue_id = $data['comic_vine_issue_id'];
            $comic->save();

            Cache::forget('_index_comics_user_id_'.$this->currentUser['id']);
            Cache::forget('_show_comic_id_'.$id.'_user_id_'.$this->currentUser['id']);

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

        $comic = $this->currentUser->comics()->find($id);
        if($comic) {
            $series_id = $comic['series']['id'];
            $this->currentUser->comics()->find($id)->delete();
            $comic_count = Series::find($series_id)->comics()->get()->count();
            if($comic_count == 0) {
                Series::find($series_id)->delete();
                Cache::forget('_index_series_user_id_'.$this->currentUser['id']);
                Cache::forget('_show_series_id_'.$series_id.'_user_id_'.$this->currentUser['id']);
            }

            Cache::forget('_index_comics_user_id_'.$this->currentUser['id']);
            Cache::forget('_show_comic_id_'.$id.'_user_id_'.$this->currentUser['id']);

            return $this->respondSuccessful('Comic Deleted');
        }
        return $this->respondNotFound('No Comic Found');

	}

    /**
     * Query Comic Vine API
     *
     * @param $id
     * @return mixed
     */
    public function getMeta($id){
        $comic = $this->currentUser->comics()->with('series')->find($id);

        if($comic) {

            if(!$comic->series->comic_vine_series_id){
                return $this->respondBadRequest('No Comic Vine Series ID set on parent series');
            }

            $guzzle = $this->guzzle;//New Guzzle;

            $comic_vine_api_url = 'http://comicvine.com/api/issues/';

            $limit = 20; //max is 100
            $offset = $limit * (Input::get('offset') ? (Input::get('offset') - 1): 0);
            $issue = (Input::get('issue') ? Input::get('issue') : '');//Should use issue data from db somehow//($comic->issue ? $comic->issue : ''));
            $comic_vine_volume_id = $comic->series->comic_vine_series_id;

            $response = Cache::remember('_comic_vine_issue_query_'.$comic_vine_volume_id.'_offset_'.$offset.= ($issue ? '_issue_'.$issue : ''), 10, function() use($guzzle, $comic_vine_api_url, $limit, $offset, $issue, $comic_vine_volume_id) {//TODO:Consider Cache time

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
                return $this->respondBadRequest('Comic Vine API Error');
                //TODO: Notify Admin //json_decode($response->getBody(), true)['error']
            }
            $comic_vine_query = $response['results'];

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

            return $this->respond([
                'issues' => $issues
            ]);
        }
        return $this->respondNotFound('No Comic Found');

    }

}
