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

}
