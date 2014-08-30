<?php

class DatabaseSeeder extends Seeder {

	/**
	 * Run the database seeds.
	 *
	 * @return void
	 */
	public function run()
	{
		Eloquent::unguard();
		//Upload::truncate();
		$this->call('UsersTableSeeder');
		$this->call('UploadsTableSeeder');
		$this->call('CollectionsTableSeeder');
        $this->call('ComicImagesTableSeeder');
		$this->call('SeriesTableSeeder');
		$this->call('ComicsTableSeeder');

	}

}
