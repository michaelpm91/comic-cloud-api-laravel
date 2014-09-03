<?php

class ComicImagesController extends ApiController {

	/**
	 * Display the specified image.
	 *
	 * @param  int  $id
	 * @return Response
	 */
	public function show($comic_slug, $size = 'medium')
	{
        $userCollectionIDs = Auth::user()->comics()->lists('collection_id');

        $comicImage = ComicImage::where('image_slug', '=', $comic_slug)->first();

        $comicCollectionIDs = $comicImage->collections()->lists('collection_id');

		foreach($comicCollectionIDs as $comicCollectionID){
	        if(!in_array($comicCollectionID, $userCollectionIDs)) return $this->respondNotFound('No Image Found');
		}

		$imageUrl = getenv('AWS_Comic_Cloud_Images_URL').$comic_slug.'_'.strtolower($size).'.jpg';
        $image = Image::make($imageUrl);

        return $image->response();
	}

}
