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
            [
                'id' => 'test_client_admin_id',
                'secret' => 'test_client_admin_secret',
                'name' => 'test_client',
                'created_at' => $datetime,
                'updated_at' => $datetime,
            ],
            [
                'id' => 'test_processor_id',
                'secret' => 'test_processor_secret',
                'name' => 'test_processor',
                'created_at' => $datetime,
                'updated_at' => $datetime,
            ],
        ];

        DB::table('oauth_clients')->insert($clients);

        $oauth_scopes = [
            [
                'id' => 'basic',
                'description' => 'Scope for all',
                'created_at' => $datetime,
                'updated_at' => $datetime,
            ],
            [
                'id' => 'admin',
                'description' => 'Scope for Admins',
                'created_at' => $datetime,
                'updated_at' => $datetime,
            ],
            [
                'id' => 'processor',
                'description' => 'Scope for Processor',
                'created_at' => $datetime,
                'updated_at' => $datetime,
            ]
        ];

        DB::table('oauth_scopes')->insert($oauth_scopes);

        $oauth_grants = [
            [
                'id' => 'client_credentials',
                'created_at' => $datetime,
                'updated_at' => $datetime,
            ],
            [
                'id' => 'password',
                'created_at' => $datetime,
                'updated_at' => $datetime,
            ],
            [
                'id' => 'password_admin',
                'created_at' => $datetime,
                'updated_at' => $datetime,
            ],
            [
                'id' => 'refresh_token',
                'created_at' => $datetime,
                'updated_at' => $datetime,
            ],
        ];

        DB::table('oauth_grants')->insert($oauth_grants);

        $oauth_grant_scopes = [
            [
                'scope_id' => 'basic',
                'grant_id' => 'password',
                'created_at' => $datetime,
                'updated_at' => $datetime,
            ],
            [
                'scope_id' => 'processor',
                'grant_id' => 'client_credentials',
                'created_at' => $datetime,
                'updated_at' => $datetime,
            ],
            [
                'scope_id' => 'admin',
                'grant_id' => 'password_admin',
                'created_at' => $datetime,
                'updated_at' => $datetime,
            ],
            [
                'scope_id' => 'basic',
                'grant_id' => 'refresh_token',
                'created_at' => $datetime,
                'updated_at' => $datetime,
            ],
            [
                'scope_id' => 'admin',
                'grant_id' => 'refresh_token',
                'created_at' => $datetime,
                'updated_at' => $datetime,
            ]

        ];

        DB::table('oauth_grant_scopes')->insert($oauth_grant_scopes);

        $oauth_client_grants = [
            [
                'client_id' => 'test_client_id',
                'grant_id' => 'password',
                'created_at' => $datetime,
                'updated_at' => $datetime,
            ],
            [
                'client_id' => 'test_client_id',
                'grant_id' => 'refresh_token',
                'created_at' => $datetime,
                'updated_at' => $datetime,
            ],
            [
                'client_id' => 'test_client_admin_id',
                'grant_id' => 'password_admin',
                'created_at' => $datetime,
                'updated_at' => $datetime,
            ],
            [
                'client_id' => 'test_processor_id',
                'grant_id' => 'client_credentials',
                'created_at' => $datetime,
                'updated_at' => $datetime,
            ],
        ];

        DB::table('oauth_client_grants')->insert($oauth_client_grants);

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
