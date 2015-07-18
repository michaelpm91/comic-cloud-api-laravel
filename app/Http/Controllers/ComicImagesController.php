<?php namespace App\Http\Controllers;

use App\ComicBookArchive;
use App\Http\Controllers\Controller;


use App\ComicImage;

use Image;
use Storage;
use Request;
use Input;
use Validator;

use Illuminate\Pagination\LengthAwarePaginator;

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
    /**
     * Display the specified resource.
     *
     * @param $image_slug
     * @param string $size
     * @internal param int $id
     * @return Response
     */
	public function show($image_slug){

        $size = (Input::get('size')? (is_numeric(Input::get('size'))? Input::get('size') : 500) : 500);//TODO: Extract this to global config

        $comicImage = ComicImage::where('image_slug', '=', $image_slug)->first();

        if(!$comicImage) {
            return $this->respondNotFound([
                'title' => 'Image Not Found',
                'detail' => 'Image Not Found',
                'status' => 404,
                'code' => ''
            ]);
        }

        $userCbaIds = $this->currentUser->comics()->lists('comic_book_archive_id');
        $comicCbaIds = $comicImage->comicBookArchives()->lists('comic_book_archive_id');

        foreach($comicCbaIds as $comicCbaId){
            if(!in_array($comicCbaId, $userCbaIds)) {
                return $this->respondNotFound([
                    'title' => 'Image Not Found',
                    'detail' => 'Image Not Found',
                    'status' => 404,
                    'code' => ''
                ]);
            }
        }

        $img = Image::make($comicImage->image_url);

        $imgCache = Image::cache(function($image) use ($img, $size) {
            $image->make($img)->interlace()->resize(null, $size, function ($constraint) { $constraint->aspectRatio(); $constraint->upsize(); });
        }, 60, true);

        return $imgCache->response();

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
