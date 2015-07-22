<?php namespace App\Http\Controllers;

use App\Http\Requests;
use App\Http\Controllers\Controller;

use Illuminate\Http\Request;

use App\ComicImage;
use App\ComicBookArchive;

use Input;


class ComicBookArchivesController extends ApiController {

	/**
	 * Update the specified resource in storage.
	 *
	 * @param  int  $id
	 * @return Response
	 */
	public function update($id){

        $request = Input::json()->all();

        $cba = ComicBookArchive::find($id);


        if(!$cba){
            return $this->respondNotFound([
                'title' => 'Comic Book Archive Not Found',
                'detail' => 'Comic Book Archive Not Found',
                'status' => 404,
                'code' => ''
            ]);
        }

        if($request['attach_image_id']) {

            $comic_image = ComicImage::find($request['attach_image_id']);

            if (!$comic_image) {
                return $this->respondNotFound([
                    'title' => 'Comic Image Not Found',
                    'detail' => 'Comic Image  Not Found',
                    'status' => 404,
                    'code' => ''
                ]);
            }

            $comic_image->comicBookArchives()->attach($id);

            return $this->respondNoContent();
        }else if(isset($request['comic_book_archive_status'])){
            if($request['comic_book_archive_status'] == 1){
                if(!isset($request['comic_book_archive_contents'])){
                    return $this->respondBadRequest([
                        'title' => 'Missing Required Field Or Incorrectly Formatted Dat',
                        'detail' => 'Comic Book Archive Contents cannot be empty',
                        'status' => 400,
                        'code' => ''
                    ]);
                }
                //post new status and json to database
                //cascade across related comics
                //return no content

            }else if ($request['comic_book_archive_status'] == 2){
                //post new status and json to database
                //cascade across related comics
                //return no content
            }

        }

    }

}
