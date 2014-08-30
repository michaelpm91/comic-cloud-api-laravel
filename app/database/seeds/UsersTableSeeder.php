<?php

class UsersTableSeeder extends Seeder {

	public function run()
	{
		User::create([
			'email' => 'user1',
			'password' => Hash::make('1234')
		]);
		User::create([
			'email' => 'user2',
			'password' => Hash::make('1234')
		]);
	}

}
