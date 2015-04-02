<?php namespace App\Commands;

use App\User;
use App\ComicBookArchive;
use App\Upload;
use App\Comic;
use App\Series;
use App\Commands\Command;

use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldBeQueued;

use Illuminate\Contracts\Bus\SelfHandling;

use Log;
use Storage;

class ProcessComicBookArchiveCommand extends Command implements ShouldBeQueued, SelfHandling
{

    use InteractsWithQueue, SerializesModels;

    protected $message;
    protected $user_id;


    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct($message){
        $this->message = $message;
    }
    /**
     *
     */
    public function handle(){
        $process_info = $this->message;
        $this->user_id = $process_info['user_id'];

        $cba = ComicBookArchive::where('comic_book_archive_hash', '=', $process_info['hash'])->first();

        if (!$cba) $cba = $this->createComicBookArchive();

        $comic_info = $this->getComicInfo($process_info['upload_id']);
        $comic_info['comic_book_archive_id'] = $cba->id;

        $this->createComic($comic_info);

        $this->processArchive($process_info['upload_id']);

    }

    /**
     * @return ComicBookArchive
     */
    private function createComicBookArchive(){
        $process_info = $this->message;

        $cba = new ComicBookArchive();
        $cba->upload_id = $process_info['upload_id'];
        $cba->comic_book_archive_hash = $process_info['hash'];
        $cba->comic_book_archive_status = 0;
        $cba->save();

        return $cba;
    }

    /**
     * @param $comic_info
     * @return Comic
     */
    private function createComic($comic_info){

        $cba = ComicBookArchive::findOrFail($comic_info['comic_book_archive_id']);

        $newComicID = (Comic::find($comic_info['comic_id']) ? str_random(40) : $comic_info['comic_id']);//If ID generated by client already exists, generate a new one

        $comic = new Comic;
        $comic->id = $newComicID;
        $comic->comic_issue = $comic_info['comic_issue'];
        $comic->comic_writer = $comic_info['comic_writer'];
        $comic->comic_book_archive_contents = (($cba->comic_book_archive_contents ? $cba->comic_book_archive_contents : ''));
        $comic->user_id = $this->user_id;
        $comic->series_id = $comic_info['series_id'];
        $comic->comic_book_archive_id = $cba->id;
        $comic->comic_status = $cba->comic_book_archive_status;
        $comic->save();

        return $comic;
    }

    /**
     * @param $match_data
     * @return Series
     */
    private function createSeries($match_data){

        $series = new Series;
        $newSeriesID = (Series::find($match_data['series_id']) ? str_random(40) : $match_data['series_id']);//If ID generated by client already exists, generate a new one

        $series->id = $newSeriesID;
        $series->series_title = $match_data['series_title'];
        $series->series_start_year = $match_data['series_start_year'];
        $series->series_publisher = 'Unknown';
        $series->user_id = $this->user_id;
        $series->save();

        return $series;
    }

    /**
     * @param $upload_id
     * @return array
     */
    private function getComicInfo($upload_id){

        $upload = Upload::findOrFail($upload_id);//TODO: Decide on find of find or fail

        $match_data = json_decode($upload->match_data, true);

        $series_id = $this->getSeriesInfo($match_data);

        return $comicInfo = [
            'comic_issue' => $match_data['comic_issue'],
            'comic_id' => $match_data['comic_id'],
            'series_id' => $series_id,
            'comic_writer' => 'Unknown'
        ];

    }

    /**
     * @param $match_data
     * @return mixed
     */
    private function getSeriesInfo($match_data){
        //dd($this->user_id);
        $series = User::find($this->user_id)->first()->series()->find($match_data['series_id']);

        if ($match_data['exists'] == false && $series == null) {

            $series = $this->createSeries($match_data);

        }
        return $series->id;

    }

    private function processArchive($upload_id){
        $upload_obj = Upload::find($upload_id);

        //download archive
        if(Storage::disk(env('user_uploads'))->exists($upload_obj->file_upload_name)){
            $withoutExt = preg_replace('/\\.[^.\\s]{3,4}$/', '', $upload_obj->file_upload_name);//TODO: This should probably be split on upload and handled DB side.
            $cba = Storage::disk(env('user_uploads'))->get($upload_obj->file_upload_name);
            $archive_extract_area = $withoutExt.'/archive/'.$upload_obj->file_upload_name;
            Storage::disk(env('cba_extraction_area'))->put($archive_extract_area, $cba);


        }


        //Determine Archive Type and begin extraction

        //Pass extracted Image through to process function

        //Delete extraction zone.
        //Storage::disk(env('cba_extraction_area'))->delete('file.jpg');//Something like this?
    }

    private function processImage(){

    }

}
