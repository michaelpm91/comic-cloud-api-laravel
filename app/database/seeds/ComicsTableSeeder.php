<?php

// Composer: "fzaninotto/faker": "v1.3.0"
use Faker\Factory as Faker;

class ComicsTableSeeder extends Seeder {

	public function run()
	{
		$faker = Faker::create();
		
		$collectionIDs = Collection::lists('id');

		foreach(range(1, 30) as $index)
		{
			$collection = Collection::find($faker->randomElement($collectionIDs));

			$userid = $collection->upload()->first()->user()->first()->id;
			$userSeries = $collection->upload()->first()->user()->first()->series()->lists('id');
            //$this->command->info('Collection: ' . $collection);
			Comic::create([
				'comic_issue' => rand(1,100),
				'comic_writer' => $faker->firstName." ".$faker->lastName,
				'comic_collection' => $collection->collection_contents,
				'user_id' => $userid,
				'series_id' => $faker->randomElement($userSeries),
				'collection_id' => $collection->id,
                'comic_status' => 1
			]);
		}
	}

}
