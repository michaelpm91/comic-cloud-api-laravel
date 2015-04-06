<?php namespace App\Http\Controllers;

use App\Http\Controllers\Controller;


use App\ComicImage;

use Image;
use Storage;
use Request;

class ComicImagesController extends ApiController {
    /**
     * Display the specified resource.
     *
     * @param $comic_slug
     * @param string $size
     * @internal param int $id
     * @return Response
     */
	public function show($comic_slug, $size = '500'){

        $comicImage = ComicImage::where('image_slug', '=', $comic_slug)->first();

        if(!$comicImage) return $this->respondNotFound('No Image Found');

        $userCbaIds = $this->currentUser->comics()->lists('comic_book_archive_id');
        $comicCbaIds = $comicImage->comicBookArchives()->lists('comic_book_archive_id');

        foreach($comicCbaIds as $comicCbaId){
            if(!in_array($comicCbaId, $userCbaIds)) return $this->respondNotFound('No Image Found');
        }

        $img = Storage::disk(env('user_images', 'local_user_images'))->get($comicImage->image_slug.".jpg");//TODO: Hard coded file type
        $size = (is_numeric($size)? $size : 500);

        $imgCache = Image::cache(function($image) use ($img, $size) {
            $image->make($img)->interlace()->resize(null, $size, function ($constraint) { $constraint->aspectRatio(); $constraint->upsize(); });
        }, 60, true);

        return $imgCache->response();

	}
}
