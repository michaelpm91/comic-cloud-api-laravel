<?php

use Illuminate\Database\Seeder;
use Illuminate\Database\Eloquent\Model;

use Carbon\Carbon;

Use App\User;

class TestSeeder extends Seeder {


	public function run()
	{
        User::create([
            'username' => 'test_user',
            'email' => 'test@comiccloud.io',
            'password' => Hash::make('1234')
        ]);


        $datetime = Carbon::now();

        $clients = [
            [
                'id' => 'test_client_id',
                'secret' => 'test_client_secret',
                'name' => 'test_client',
                'created_at' => $datetime,
                'updated_at' => $datetime,
            ],
        ];

        DB::table('oauth_clients')->insert($clients);

        $sessions = [
            [
                'client_id' => 'test_client_id',
                'owner_id'  => 1,
                'owner_type' => 'user',
                'created_at' => $datetime,
                'updated_at' => $datetime,
            ]
        ];

        DB::table('oauth_sessions')->insert($sessions);

        $tokens = [
            [
                'id' => 'test_access_token',
                'session_id'  => 1,
                'expire_time' => time() + 600,
                'created_at' => $datetime,
                'updated_at' => $datetime,
            ]
        ];

        DB::table('oauth_access_tokens')->insert($tokens);

        $refreshtokens = [
            [
                'id' => 'test_refresh_token',
                'access_token_id' => 'test_access_token',
                'expire_time' => time() + 600,
                'created_at' => $datetime,
                'updated_at' => $datetime,
            ],
        ];

        DB::table('oauth_refresh_tokens')->insert($refreshtokens);
	}

}
