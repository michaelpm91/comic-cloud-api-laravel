<?php
//todo-mike Change to match new DB schema
// Composer: "fzaninotto/faker": "v1.3.0"
use Faker\Factory as Faker;

class SeriesTableSeeder extends Seeder {

	public function run()
	{
		$faker = Faker::create();
		
		$userIDs = User::lists('id');

		foreach(range(1, 50) as $index)
		{
			Series::create([
				'series_title' => $faker->sentence(rand(3,6)),
				'series_start_year' => $faker->year(),
				'series_publisher' => $faker->word(),
				'user_id' => $faker->randomElement($userIDs)
			]);
		}
	}

}
