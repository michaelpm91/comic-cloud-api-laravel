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

        //return $userCollectionIDs;
        return $userCollectionIDs;
        if(in_array($comicCollectionIDs, $userCollectionIDs)) return 'yow';
        /*if(!$comicImage || !in_array($comicImage->collection_id, $userCollectionIDs)){
            return $this->respondNotFound('No Image Found');
        }

        $image = Image::make($comicImage->image_url);

        return $image->response();*/
	}

}
