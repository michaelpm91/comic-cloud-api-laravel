<?php namespace App\Http\Controllers\Processor;

use App\Http\Requests;
use App\Http\Controllers\Controller;

use Illuminate\Http\Request;

use App\Models\ComicImage;
use App\Models\ComicBookArchive;
use App\Models\Comic;

use Input;


class ComicBookArchivesController extends ApiController {

    public function show($id){

        $comic_book_archive = ComicBookArchive::find($id);

        if(!$comic_book_archive){
            return $this->respondNotFound([
                'title' => 'Comic Book Archive Not Found',
                'detail' => 'Comic Book Archive Not Found',
                'status' => 404,
                'code' => ''
            ]);
        }

        return $this->respond([
            'comic_book_archive' => [$comic_book_archive]
        ]);

    }

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

        if(isset($request['attach_image_id'])) {

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
            if($request['comic_book_archive_status'] == 1) {
                if (!isset($request['comic_book_archive_contents'])) {
                    return $this->respondBadRequest([
                        'title' => 'Missing Required Field Or Incorrectly Formatted Dat',
                        'detail' => 'Comic Book Archive Contents cannot be empty',
                        'status' => 400,
                        'code' => ''
                    ]);
                }

                $comic_book_archive_contents = json_encode($request['comic_book_archive_contents']);
            }

            $cba->comic_book_archive_status = $request['comic_book_archive_status'];
            if($request['comic_book_archive_status'] == 1) {
                $cba->comic_book_archive_contents = $comic_book_archive_contents;
            }
            $cba->save();
            if($request['comic_book_archive_status'] == 1) {
                Comic::where('comic_book_archive_id', '=', $id) ->update(['comic_book_archive_contents' => $comic_book_archive_contents]);
            }
            return $this->respondNoContent();
        }

    }

}
