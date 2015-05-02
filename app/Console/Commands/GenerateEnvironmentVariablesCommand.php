<?php namespace App\Console\Commands;

use Illuminate\Console\Command;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Input\InputArgument;

use AWS;

class GenerateEnvironmentVariablesCommand extends Command {

	/**
	 * The console command name.
	 *
	 * @var string
	 */
	protected $name = 'genenvvar';

	/**
	 * The console command description.
	 *
	 * @var string
	 */
	protected $description = 'Fetches Laravel Environment Variables from AWS DynamoDB and outputs to an environment file';

	/**
	 * Create a new command instance.
	 *
	 * @return void
	 */
	public function __construct()
	{
		parent::__construct();
	}

	/**
	 * Execute the console command.
	 *
	 * @return mixed
	 */
	public function fire()
	{

        $aws = AWS::get('DynamoDb');
        $env = $this->argument('env');
        $env_vars = [];

        $iterator = $aws->getIterator('Scan', array(
            'TableName' => 'ComicCloudEnvironmentVariables',
            'ScanFilter' => array(
                'Environment Variable Environment' => array(
                    'AttributeValueList' => array(
                        array('S' => $env)
                    ),
                    'ComparisonOperator' => 'EQ'
                )
            )
        ));

        if(!iterator_count($iterator)) $this->error('No results Found');

        foreach ($iterator as $item) {
            $env_vars[] = reset($item['Environment Variable Key'])."=".reset($item['Environment Variable Value']);
        }

        $iterator = $aws->getIterator('Scan', array(
            'TableName' => 'ComicCloudEnvironmentVariables',
            'ScanFilter' => array(
                'Environment Variable Environment' => array(
                    'AttributeValueList' => array(
                        array('S' => 'all')
                    ),
                    'ComparisonOperator' => 'EQ'
                )
            )
        ));

        if(!iterator_count($iterator)) $this->error('No results Found');

        foreach ($iterator as $item) {
            $env_vars[] = reset($item['Environment Variable Key'])."=".reset($item['Environment Variable Value']);
        }

        file_put_contents('.env', implode("\n",$env_vars));

    }

	/**
	 * Get the console command arguments.
	 *
	 * @return array
	 */
	protected function getArguments()
	{
		return [
			['env', InputArgument::OPTIONAL, 'Desired environment.', 'production']
		];
	}

	/**
	 * Get the console command options.
	 *
	 * @return array
	 */
	protected function getOptions()
	{
		return [
			//['example', null, InputOption::VALUE_OPTIONAL, 'An example option.', null],
		];
	}

}
