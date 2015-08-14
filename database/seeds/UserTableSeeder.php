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
            'id' => 1,
            'username' => 'test_'.env('APP_ENV').'_user',
            'email' => 'test_user@comiccloud.io',
            'password' => Hash::make(env('test_user_password'))
        ]);

        User::create([
            'id' => 2,
            'username' => 'test_'.env('APP_ENV').'_admin',
            'email' => 'test_admin@comiccloud.io',
            'type' => 'admin',
            'password' => Hash::make(env('test_user_password'))
        ]);
    }
}
