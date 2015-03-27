<?php namespace App\Commands;

use App\Commands\Command;

use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldBeQueued;

use Illuminate\Contracts\Bus\SelfHandling;

use Log;

class ProcessComicBookArchiveCommand extends Command implements ShouldBeQueued, SelfHandling{

	use InteractsWithQueue, SerializesModels;

    public $message;

	/**
	 * Create a new command instance.
	 *
	 * @return void
	 */
	public function __construct($message)
	{
		//
        //Log::info('Construct Queue Message:' . $message);
        $this->message = $message;
        //Log::info('Construct Queue Message:' . $message);

        Log::info('Construct Queue Message:' . implode(', ', $message));
	}

    public function handle(){
        //dd($this->message);
        Log::info('Handle Queue Message:' . implode(', ', $this->message));
    }

}
