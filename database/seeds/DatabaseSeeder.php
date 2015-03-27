<?php

use Illuminate\Database\Seeder;
use Illuminate\Database\Eloquent\Model;

class DatabaseSeeder extends Seeder {

	/**
	 * Run the database seeds.
	 *
	 * @return void
	 */
	public function run()
	{
		Model::unguard();

		// $this->call('UserTableSeeder');
        if (env('APP_ENV') === 'testing' || env('APP_ENV') === 'local'){
            $this->call('TestSeeder');
        }
	}

}
