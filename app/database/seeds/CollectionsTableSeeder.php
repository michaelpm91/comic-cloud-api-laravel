<?php

// Composer: "fzaninotto/faker": "v1.3.0"
use Faker\Factory as Faker;

class CollectionsTableSeeder extends Seeder {

	public function run()
	{		
		$faker = Faker::create();

		$uploadIDs = Upload::lists('id');

		foreach($uploadIDs as $index)
		{
			/*$pages = rand(15, 25);
			$collectionArray = [];
			for ($i = 1; $i <= $pages; $i++) { 
				$collectionArray[] = $faker->imageUrl(1275,1650, 'cats');
			}*/
			Collection::create([
				'upload_id' => $index,
				//'collection_contents' => json_encode($collectionArray),
				'collection_hash' => $faker->md5(),
                'collection_status' => 1
			]);
		}
	}

}
