<?php

class ComicImagesController extends ApiController {

    public function __construct(){
        $user = User::find(ResourceServer::getOwnerId());
        Auth::login($user);
    }
	/**
	 * Display the specified image.
	 *
	 * @param  int  $id
	 * @return Response
	 */
    public function show($comic_slug, $size = 'medium'){

        $userCollectionIDs = Auth::user()->comics()->lists('collection_id');

        $comicImage = ComicImage::where('image_slug', '=', $comic_slug)->first();

        $comicCollectionIDs = $comicImage->collections()->lists('collection_id');

        foreach($comicCollectionIDs as $comicCollectionID){
            if(!in_array($comicCollectionID, $userCollectionIDs)) return $this->respondNotFound('No Image Found');
        }
        $imageUrl = getenv('AWS_Comic_Cloud_Images_URL').$comic_slug.'.jpg';
        $size = (is_numeric($size)? $size : 1000);

        $img = Image::cache(function($image) use ($imageUrl, $size) {
            $image->make($imageUrl)->interlace()->resize(null, $size, function ($constraint) { $constraint->aspectRatio(); $constraint->upsize(); });
        }, 60, true);

        return $img->response();
    }

}
