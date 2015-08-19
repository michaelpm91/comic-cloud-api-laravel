<?php namespace App\Http\Controllers\Processor;

use App\Models\ComicBookArchive;
use App\Models\ComicImage;

use Image;
use Storage;
use Request;
use Input;
use Validator;

use Illuminate\Pagination\LengthAwarePaginator;
use App\Http\Controllers\ApiController;


class ComicImagesController extends ApiController {

    /**
     * Returns all images.
     *
     * This route is only available for processor clients
     *
     * @return mixed
     */
    public function index(){//

        $queryFilters = Input::get();
        $comicImages = ComicImage::where($queryFilters)->paginate(env('paginate_per_page'))->toArray();
        $comicImages['images'] = $comicImages['data'];
        unset($comicImages['data']);
        return $this->respond($comicImages);
    }

    public function store(){
        //if(!Request::isJson()) dd('nooo');//Header Content Type Check

        $request = Input::json()->all();


        Validator::extend('valid_uuid', function($attribute, $value, $parameters) {
            if(preg_match("/^(\{)?[a-f\d]{8}(-[a-f\d]{4}){4}[a-f\d]{8}(?(1)\})$/i", $value)) {
                return true;
            } else {
                return false;
            }
        });

        $messages = [
            'image_slug.valid_uuid' => 'The :attribute field is not a valid ID.',
        ];

        $validator = Validator::make($request, [
            'image_slug' => 'required|valid_uuid',
            'image_size' => 'required|numeric',
            'image_url' => 'required|url',
            'image_hash' => 'required',
            'related_comic_book_archive_id' => 'required|numeric'
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

        $cba = ComicBookArchive::find($request['related_comic_book_archive_id']);

        if($cba) {


            $imageentry = new ComicImage;

            $imageentry->image_slug = $request['image_slug'];
            $imageentry->image_hash = $request['image_hash'];
            $imageentry->image_url = $request['image_url'];
            $imageentry->image_size = $request['image_size'];
            $imageentry->save();

            $imageentry->comicBookArchives()->attach($request['related_comic_book_archive_id']);

            return $this->respondCreated([
                'images' => [$imageentry]
            ]);

        }

        return $this->respondBadRequest([[
            'title' => 'Invalid Comic Book Archive ID',
            'detail' => 'Invalid Comic Book Archive ID',
            'status' => 400,
            'code' => ''
        ]]);

    }

}
