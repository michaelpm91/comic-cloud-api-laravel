<?php
//todo-mike Change to match new DB schema
// Composer: "fzaninotto/faker": "v1.3.0"
use Faker\Factory as Faker;

class UploadsTableSeeder extends Seeder {

	public function run()
	{
		$faker = Faker::create();

		$userIDs = User::lists('id');

		foreach(range(1, 30) as $index)
		{
			$randomCBAExtension = ['cbr','cba'];	
			$randomExtension = $faker->randomElement($randomCBAExtension);//$randonCBAExtension[array_rand($randonCBAExtension)];//$faker->fileExtension;
			Upload::create([
				'user_id' => $faker->randomElement($userIDs),
				'file_original_name' => str_replace(' ', '_', $faker->sentence(rand(1,4))).$randomExtension,
				'file_size' => $faker->numberBetween(1000,1000000),
				'file_upload_name' => str_random(40).".".$randomExtension 
			]);
		}
	}

}
