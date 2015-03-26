<?php namespace App\Handlers\Commands;

use App\Commands\ProcessComicBookArchiveCommand;

use Illuminate\Queue\InteractsWithQueue;

use Log;

class ProcessComicBookArchiveCommandHandler {

    /**
     * Create the command handler.
     *
     * @return void
     */
    public function __construct()
    {
        //
        Log::info('Command Handler Construct');
    }

    /**
     * Handle the command.
     *
     * @param  ProcessComicBookArchiveCommand  $command
     * @return void
     */
    public function handle(ProcessComicBookArchiveCommand $command)
    {
        //dd($command);
        Log::info('Handle Queue Message:' . implode(', ', $command->message));
    }

}
