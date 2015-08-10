<?php namespace App\Http\Controllers\Admin;

use App\Http\Controllers\ApiController;
use App\Models\Admin\Comic;

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

}
