<?php

use Illuminate\Database\Seeder;

Use App\Models\User;

class UserTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        User::create([
            'username' => 'kidshenlong',
            'email' => 'test@comiccloud.io',
            'password' => Hash::make(env('test_user_password'))
        ]);

        User::create([
            'username' => 'test_'.env('APP_ENV').'_user',
            'email' => 'test@comiccloud.io',
            'password' => Hash::make(env('test_user_password'))
        ]);
    }
}
