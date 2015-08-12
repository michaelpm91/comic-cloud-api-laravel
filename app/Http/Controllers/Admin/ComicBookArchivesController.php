<?php namespace App\Http\Controllers\Admin;

use App\Http\Controllers\ApiController;
use App\Models\Admin\ComicBookArchive;

class ComicBookArchivesController extends ApiController {


    /**
     * @return mixed
     */
    public function index(){

        $comic_book_archive = ComicBookArchive::paginate(env('paginate_per_page'))->toArray();

        $comic_book_archive['comic_book_archive'] = $comic_book_archive['data'];
        unset($comic_book_archive['data']);

        return $this->respond($comic_book_archive);
    }

    /**
     * Display the specified comic_book_archive.
     *
     * @param  int  $id
     * @return Response
     */
    public function show($id)
    {

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
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return Response
     */
    public function destroy($id){

        $comic_book_archive = ComicBookArchive::find($id);

        if($comic_book_archive){
            $comic_book_archive->delete();
            return $this->respondSuccessful('Comic Book Archive Deleted');

        }else{
            return $this->respondNotFound([
                'title' => 'Comic Book Archive Not Found',
                'detail' => 'Comic Book Archive Not Found',
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

        $comic_book_archive = ComicBookArchive::find($id);

        if($comic_book_archive){

            $data = Request::all();

            if(empty($data)) return $this->respondBadRequest([[
                'title' => 'No Data Sent',
                'detail' => 'No Data Sent',
                'status' => 400,
                'code' => ''
            ]]);

            if (isset($data['comic_book_archive_status'])) $comic_book_archive->comic_book_archive_status = $data['comic_book_archive_status'];

            $comic_book_archive->save();

            return $this->respondSuccessful([
                'comic_book_archive' => [$comic_book_archive]
            ]);

        }else{
            return $this->respondNotFound([
                'title' => 'Comic Book Archive Not Found',
                'detail' => 'Comic Book Archive Not Found',
                'status' => 404,
                'code' => ''
            ]);
        }
    }


}
