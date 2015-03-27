<?php

use Illuminate\Database\Seeder;
use Illuminate\Database\Eloquent\Model;

use Carbon\Carbon;

Use App\User;

class LocalSeeder extends Seeder {


	public function run()
	{
        User::create([
            'username' => 'local_user',
            'email' => 'local@comiccloud.io',
            'password' => Hash::make('1234')
        ]);


        $datetime = Carbon::now();

        $clients = [
            [
                'id' => 'local_client_id',
                'secret' => 'local_client_secret',
                'name' => 'local_client',
                'created_at' => $datetime,
                'updated_at' => $datetime,
            ],
        ];

        DB::table('oauth_clients')->insert($clients);

        $sessions = [
            [
                'client_id' => 'local_client_id',
                'owner_id'  => 1,
                'owner_type' => 'user',
                'created_at' => $datetime,
                'updated_at' => $datetime,
            ]
        ];

        DB::table('oauth_sessions')->insert($sessions);

        $tokens = [
            [
                'id' => 'local_access_token',
                'session_id'  => 1,
                'expire_time' => time() + 60,
                'created_at' => $datetime,
                'updated_at' => $datetime,
            ]
        ];

        DB::table('oauth_access_tokens')->insert($tokens);

        $refreshtokens = [
            [
                'id' => 'local_refresh_token',
                'access_token_id' => 'local_access_test',
                'expire_time' => time() + 60,
                'created_at' => $datetime,
                'updated_at' => $datetime,
            ],
        ];

        DB::table('oauth_refresh_tokens')->insert($refreshtokens);
	}

}
