<?php namespace App\Commands;

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

class ProcessComicBookArchiveCommand extends Command implements ShouldBeQueued, SelfHandling{

	use InteractsWithQueue, SerializesModels;

    protected $message;
    protected $user_id;


	/**
	 * Create a new command instance.
	 *
	 * @return void
	 */
	public function __construct($message)
	{
        $this->message = $message;
	}

    public function handle(){
        $process_info = $this->message;
        $this->user_id = $process_info['user_id'];
        $cba = ComicBookArchive::where('comic_book_archive_hash', '=', $process_info['hash'])->first();
        $processArchive = false;

        if(!$cba){
            $cba = new ComicBookArchive();
            $cba->upload_id = $process_info['upload_id'];
            $cba->comic_book_archive_hash = $process_info['hash'];
            $cba->comic_book_archive_status = 0;
            $cba->save();
            $processArchive = true;
        }

        //$this->collection_id = $cba->id;

        $this->createComic($process_info['upload_id'], $cba);

        if($processArchive){
            //Log::info('Process Archive');
            //$this->processArchive($process_info);
        }


    }

    protected function createComic($upload_id, $cba){

        $comic_info = $this->getComicInfo($upload_id);

        $newComicID = $comic_info['comic_id'];
        $comic = new Comic;
        $comic->id = $newComicID;
        $comic->comic_issue = $comic_info['comic_issue'];
        $comic->comic_writer = $comic_info['comic_writer'];
        $comic->comic_book_archive_contents = (($cba->comic_book_archive_contents ? $cba->comic_book_archive_contents : '' ));
        $comic->user_id = $this->user_id;
        $comic->series_id = $comic_info['series_id'];
        $comic->comic_book_archive_id = $cba->id;
        $comic->comic_status = $cba->comic_book_archive_status;
        $comic->save();

    }

    protected function getSeriesInfo($seriesTitle){//todo-mike: make sure this function only returns series that user has access to.

        $series = User::find($this->user_id)->first()->series()->where('series_title', '=', $seriesTitle)->first();//todo-mike: this isn't returning user specific series

        if(!$series){
            $series = new Series;
            $series->id = str_random(40);
            $series->series_title = $seriesTitle;
            $series->series_start_year = '0000';
            $series->series_publisher = 'Unknown';
            $series->user_id = $this->user_id;
            $series->save();
        }

        return $series->id;

    }

    protected function getComicInfo($upload_id){
        $upload = Upload::findOrFail($upload_id);//TODO: Decide on find of find or fail
        $match_data = json_decode($upload->match_data, true);
        $series_id = getSeriesInfo();
        if($match_data['exists']){
            $comicInfo = ['comic_issue' => $match_data['comic_issue'], 'comic_id' => $match_data['comic_id'], 'series_id' => $match_data['series_id'], 'comic_writer' => 'Unknown'];
        }else{
            $series = new Series;
            $series->id = $match_data['series_id'];
            $series->series_title = $match_data['series_title'];
            $series->series_start_year = $match_data['series_start_year'];
            $series->series_publisher = 'Unknown';
            $series->user_id = $upload->user_id;
            $series->save();
            $comicInfo = ['comic_issue' => $match_data['comic_issue'], 'comic_id' => $match_data['comic_id'], 'series_id' => $match_data['series_id'], 'comic_writer' => 'Unknown'];
        }
        return $comicInfo;
    }

}
