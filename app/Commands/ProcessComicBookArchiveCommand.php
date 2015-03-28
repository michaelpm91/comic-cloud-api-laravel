<?php namespace App\Commands;

use App\ComicBookArchive;
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

    }

}
