<?php namespace App\Http\Controllers;

use App\Http\Requests;
use App\Http\Controllers\Controller;

use Illuminate\Http\Request;

use App\ComicImage;
use App\ComicBookArchive;

use Validator;
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

        Validator::extend('image_id_exist', function($attribute, $value, $parameters) use ($id)  {
            $comic_image = ComicImage::find($id);
            if($comic_image) {
                return true;
            } else {
                return false;
            }
        });

        $messages = [
            'attach_image_id.image_id_exists' =>  'Not a valid Image ID'
        ];

        $validator = Validator::make($request, [
            'attach_image_id' => 'numeric|image_id_exist'
        ], $messages);

        if ($validator->fails()){
            $pretty_errors = array_map(function($item){
                return [
                    'title' => 'Missing Required Field Or Incorrectly Formatted Data',
                    'detail' => $item,
                    'status' => 400,
                    'code' => ''
                ];
            }, $validator->errors()->all());

            return $this->respondBadRequest($pretty_errors);
        }

        //$cba->attach($request['attach_image_id']);
        //$imageentry->comicBookArchives()->attach($request['related_comic_book_archive_id']);
        $cba->comicImages()->


    }

}
