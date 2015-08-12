<?php namespace App\Http\Controllers\Admin;

use App\Http\Controllers\ApiController;
use App\Models\Admin\ComicImage;
use Input;

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
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return Response
     */
    public function destroy($id){

        $comicImage = ComicImage::find($id);

        if($comicImage){
            $comicImage->delete();
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
    public function update($id){

        $comicImage = ComicImage::find($id);

        if($comicImage){

            $data = Request::all();

            if(empty($data)) return $this->respondBadRequest([[
                'title' => 'No Data Sent',
                'detail' => 'No Data Sent',
                'status' => 400,
                'code' => ''
            ]]);

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
