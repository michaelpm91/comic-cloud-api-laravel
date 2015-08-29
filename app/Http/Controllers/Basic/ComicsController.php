<?php namespace App\Http\Controllers\Basic;

use App\Http\Controllers\Controller;

use App\Models\Series;
use App\Models\Comic;

use Validator;
use Request;
use Input;
use Cache;
use GuzzleHttp\Client as Guzzle;

use LucaDegasperi\OAuth2Server\Authorizer;

use App\Http\Controllers\ApiController;

use Illuminate\Pagination\LengthAwarePaginator as Paginator;


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
        $comics = $currentUser->comics()->paginate(env('paginate_per_page'))->toArray();

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

        $comic = $currentUser->comics()->find($id);


        if(!$comic){
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
	public function update($id){

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
                'series_id' => 'user_series|valid_uuid',//TODO: should allow writing of new series IDs or other related
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

            unset($data['method']);//So empty inputs can be detected correctly

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
	public function destroy($id){

        $comic = $this->currentUser->comics()->find($id);
        if($comic) {
            $series_id = $comic['series']['id'];
            $this->currentUser->comics()->find($id)->delete();
            $comic_count = Series::find($series_id)->comics()->get()->count();
            if($comic_count == 0) Series::find($series_id)->delete();

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
            if (!$comic->series->comic_vine_series_id) {
                return $this->respondBadRequest([//TODO: Detailed api error response
                    'title' => 'Bad Request',
                    'detail' => 'No Comic Vine Series ID set on parent series',
                    'status' => 400,
                    'code' => ''
                ]);
            }

            $guzzle = $this->guzzle;

            $comic_vine_api_url = env('COMIC_VINE_ISSUE_SEARCH_URL');

            $limit = 20;
            $page = (int)(Input::get('page') ? Input::get('page') : 1);
            $offset = $limit * ($page ? ($page - 1) : 0);
            $issue = (Input::get('issue') ? Input::get('issue') : '');//TODO: Should use issue data from db somehow//($comic->issue ? $comic->issue : ''));
            $comic_vine_volume_id = $comic->series->comic_vine_series_id;

            $response = Cache::remember('_comic_vine_issue_query_'.$comic_vine_volume_id.'_offset_'.$offset.= ($issue ? '_issue_'.$issue : ''), 10, function() use($guzzle, $comic_vine_api_url, $limit, $offset, $issue, $comic_vine_volume_id) {//TODO:Consider Cache time
                //TODO: Support filtering and ordering
                $queries = ['query' => [
                    'api_key' => env('comic_vine_api_key'),
                    'format' => 'json',
                    'filter' => 'volume:' . $comic_vine_volume_id .= ($issue ? ',' . 'issue_number:' . $issue : ''),
                    'limit' => $limit,
                    'offset' => $offset,
                    'field_list' => 'name,description,issue_number,volume,id,image',
                ]];
                if(env('APP_ENV') == 'testing' && Input::get('force')) $queries['query']['force'] = Input::get('force');
                $guzzle_response = $guzzle->get($comic_vine_api_url, $queries)->getBody();

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

            $last_page = ceil($response['number_of_total_results'] / $limit);

            if($page > $last_page) {

                Cache::forget('_comic_vine_issue_query_'.$comic_vine_volume_id.'_offset_'.$offset.= ($issue ? '_issue_'.$issue : ''));
                $guzzle = New Guzzle;
                $new_offset = intval($limit * ($last_page - 1));
                $response = Cache::remember('_comic_vine_issue_query_' . $comic_vine_volume_id . '_offset_'.$new_offset.= ($issue ? '_issue_'.$issue : ''), 10, function () use ($guzzle, $comic_vine_api_url, $limit, $new_offset, $issue, $comic_vine_volume_id) {//TODO:Consider Cache time
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

            $comic_vine_query = $response['results'];

            array_walk($comic_vine_query, function(&$issue_entry){
                $issue_entry = [
                    'issue_description' => (isset($issue_entry['description']) ? strip_tags($issue_entry['description']) : ''),
                    'issue_name' => $issue_entry['name'],
                    'issue_number' => $issue_entry['issue_number'],
                    'comic_vine_issue_id' => $issue_entry['id']
                ];
            });

            usort($comic_vine_query, function($a, $b) {
                return $a['issue_number'] - $b['issue_number'];
            });

            $meta = (New Paginator($comic_vine_query, $response['number_of_total_results'], $limit, $page, ['path' =>  url('v'.env('APP_API_VERSION').'/comic/'. $id .'/meta')]))->toArray();
            $meta['issue'] = $meta['data'];
            unset($meta['issue']);

            return $this->respond($meta);

        }

        return $this->respondNotFound([[
            'title' => 'Comic Not Found',
            'detail' => 'Comic Not Found',
            'status' => 404,
            'code' => ''
        ]]);

    }

    public function showRelatedSeries($comic_id){//TODO: Finish relationship
        $currentUser = $this->currentUser;

        $comic = $currentUser->comics()->find($comic_id);

        if(!$comic){
            return $this->respondNotFound([[
                'title' => 'Comic Not Found',
                'detail' => 'Comic Not Found',
                'status' => 404,
                'code' => ''
            ]]);
        }
        $series_id = $comic->series_id;

        $series = $currentUser->series()->find($series_id);

        //TODO: See if below logic is possible
        /*if(!$series){
            return $this->respondNotFound([[
                'title' => 'Series Not Found',
                'detail' => 'Series Not Found',
                'status' => 404,
                'code' => ''
            ]]);
        }*/

        return $this->respond([
            'series' => [$series]
        ]);

    }

}
