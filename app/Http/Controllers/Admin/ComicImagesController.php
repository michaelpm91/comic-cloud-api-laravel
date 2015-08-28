<?php namespace App\Http\Controllers\Admin;

use App\Http\Controllers\ApiController;
use App\Models\Admin\ComicImage;
use Input;
use Image;
use Request;
use Validator;


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

        $img = Image::make($comicImage->image_url);

        $imgCache = Image::cache(function($image) use ($img, $size) {
            $image->make($img)->interlace()->resize(null, $size, function ($constraint) { $constraint->aspectRatio(); $constraint->upsize(); });
        }, 60, true);

        return $imgCache->response();

    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return Response
     */
    public function destroy($id){//DISABLED

        $comicImage = ComicImage::find($id);//TODO: Delete by ID or SLUG

        if($comicImage){
            $comicImage->delete();
            //TODO: remove image delete or search for references and delete
            //TODO: instead of delete... maybe disable? replace image with a placeholder for replaced, with warning info.
            return $this->respondSuccessful('Comic Image Deleted');
        }else{
            return $this->respondNotFound([
                'title' => 'Comic Image Not Found',
                'detail' => 'Comic Image Not Found',
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
    public function update($image_slug){

        $comicImage = ComicImage::where('image_slug', '=', $image_slug)->first();

        if($comicImage){

            $data = Request::all();

            if(empty($data)) return $this->respondBadRequest([[
                'title' => 'No Data Sent',
                'detail' => 'No Data Sent',
                'status' => 400,
                'code' => ''
            ]]);

            //return preg_match('/^[a-f0-9]{32}$/', $md5);


            Validator::extend('valid_md5', function($attribute, $value, $parameters) {
                if(preg_match('/^[a-f0-9]{32}$/', $value)) {
                    return true;
                } else {
                    return false;
                }
            });

            $messages = [
                'image_size.valid_md5' => 'Not a valid MD5 Hash',
            ];

            $validator = Validator::make($data = Request::all(), [
                'image_size' => 'numeric',
                //'image_url' => 'active_url',//TODO: Regex?
                'image_hash' => 'valid_md5'
            ], $messages);

            if ($validator->fails()){
                $pretty_errors = array_map(function($item){
                    return [
                        'title' => 'Malformed Field',
                        'detail' => $item,
                        'status' => 400,
                        'code' => ''
                    ];
                }, $validator->errors()->all());

                return $this->respondBadRequest($pretty_errors);
            }

            if (isset($data['image_size'])) $comicImage->image_size = $data['image_size'];
            if (isset($data['image_url'])) $comicImage->image_url = $data['image_url'];
            if (isset($data['image_hash'])) $comicImage->image_hash = $data['image_hash'];

            $comicImage->save();

            return $this->respondSuccessful([
                'comic_image' => [$comicImage]
            ]);

        }else{
            return $this->respondNotFound([
                'title' => 'Comic Image Not Found',
                'detail' => 'Comic Image Not Found',
                'status' => 404,
                'code' => ''
            ]);
        }
    }
}
