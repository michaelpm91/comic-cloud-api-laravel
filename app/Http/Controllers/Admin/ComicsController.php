<?php namespace App\Http\Controllers\Admin;

use App\Http\Controllers\ApiController;
use App\Models\Admin\Comic;
use App\Models\Admin\User;

class ComicsController extends ApiController {


    /**
     * @return mixed
     */
    public function index(){

        $comics = Comic::paginate(env('paginate_per_page'))->toArray();

        $comics['comic'] = $comics['data'];
        unset($comics['data']);

        return $this->respond($comics);
    }

    /**
     * Display the specified upload.
     *
     * @param  int  $id
     * @return Response
     */
    public function show($id)
    {

        $comic = Comic::find($id);


        if(!$comic){
            return $this->respondNotFound([
                'title' => 'Comic Not Found',
                'detail' => 'Comic Not Found',
                'status' => 404,
                'code' => ''
            ]);
        }

        return $this->respond([
            'comic' => [$comic]
        ]);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return Response
     */
    public function destroy($id){

        $comic = Comic::find($id);

        if($comic){
            $comic->delete();
            return $this->respondSuccessful('Comic Deleted');

        }else{
            return $this->respondNotFound([
                'title' => 'Comic Not Found',
                'detail' => 'Comic Not Found',
                'status' => 404,
                'code' => ''
            ]);
        }

    }

    /**
     * Update the specified resource in storage.
     *
     * @param  int  $id
     * @return Response
     */
    public function update($id){

        $comic = Comic::find($id);
        if($comic){
            Validator::extend('user_series', function($attribute, $value, $parameters) use ($comic) {
                $currentUser = User::find($comic->user_id);
                if($currentUser->series()->with('comics')->find($value)){
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
            if(isset($data['comic_book_archive_contents'])) $comic->comic_book_archive_contents = $data['comic_book_archive_contents'];
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

}
