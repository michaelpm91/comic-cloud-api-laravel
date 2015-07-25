<?php

use Illuminate\Database\Seeder;
use Illuminate\Database\Eloquent\Model;

use Carbon\Carbon;

Use App\User;

class TestSeeder extends Seeder {


	public function run()
	{
        //DB::statement('Set foreign_keys = ON;');
        //if (DB::connection() instanceof Illuminate\Database\SQLiteConnection) {
        //    DB::statement(DB::raw('PRAGMA foreign_keys=1'));
        //}


        $user = User::create([
            'id' => 1,
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

        $oauth_scopes = [
            'id' => 'basic',
            'description' => 'test_scope',
            'created_at' => $datetime,
            'updated_at' => $datetime,
        ];

        DB::table('oauth_scopes')->insert($oauth_scopes);

        $scopes = [
            'client_id' => 'test_client_id',
            'scope_id' => 'basic',
            'created_at' => $datetime,
            'updated_at' => $datetime,
        ];

        DB::table('oauth_client_scopes')->insert($scopes);

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
                'expire_time' => time() + 6000000,//Should be 60
                'created_at' => $datetime,
                'updated_at' => $datetime,
            ]
        ];

        DB::table('oauth_access_tokens')->insert($tokens);

        $token_scopes = [
            'access_token_id' => 'test_access_token',
            'scope_id' => 'basic',
            'created_at' => $datetime,
            'updated_at' => $datetime,
        ];

        DB::table('oauth_access_token_scopes')->insert($token_scopes);

        $refreshtokens = [
            [
                'id' => 'test_refresh_token',
                'access_token_id' => 'test_access_token',
                'expire_time' => time() + 6000000,//Should be 60
                'created_at' => $datetime,
                'updated_at' => $datetime,
            ],
        ];

        DB::table('oauth_refresh_tokens')->insert($refreshtokens);
	}

}
