<?php

class ComicImagesController extends ApiController {

	/**
	 * Display the specified image.
	 *
	 * @param  int  $id
	 * @return Response
	 */
	public function show($comic_set_key, $size = 'medium')
	{
        $userCollectionIDs = Auth::user()->comics()->lists('collection_id');

        $comicImage = ComicImage::where('image_set_key', '=', $comic_set_key)->first();

        if(!$comicImage || !in_array($comicImage->collection_id, $userCollectionIDs)){
            return $this->respondNotFound('No Image Found');
        }

        $image = Image::make($comicImage->image_url);

        return $image->response();
	}

}
