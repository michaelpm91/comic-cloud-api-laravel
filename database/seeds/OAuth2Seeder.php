<?php

use Illuminate\Database\Seeder;

class OAuth2Seeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $datetime = Carbon::now();

        //1. Create Clients
        $web_client_id = str_random(16);
        $web_client_secret = str_random(32);
        $web_client_access_token = str_random(40);
        $web_client_refresh_token = str_random(40);

        $admin_web_client_id = str_random(16);
        $admin_web_client_secret = str_random(32);
        $admin_web_client_access_token = str_random(40);
        $admin_web_client_refresh_token = str_random(40);

        $lambda_processor_client_id = str_random(16);
        $lambda_processor_client_secret = str_random(32);
        $lambda_processor_access_token = str_random(40);
        $lambda_processor_refresh_token = str_random(40);

        $clients = [
            [
                'id' => $web_client_id,
                'secret' => $web_client_secret,
                'name' => env('APP_ENV').'.comiccloud.io',//TODO: if env is live then should be blank
                'created_at' => $datetime,
                'updated_at' => $datetime,
            ],
            [
                'id' => $admin_web_client_id,
                'secret' => $admin_web_client_secret,
                'name' => 'admin.'.env('APP_ENV').'.comiccloud.io',//TODO: if env is live then should be blank
                'created_at' => $datetime,
                'updated_at' => $datetime,
            ],
            [
                'id' =>  $lambda_processor_client_id,
                'secret' =>  $lambda_processor_client_secret,
                'name' => 'lambda_processor',
                'created_at' => $datetime,
                'updated_at' => $datetime,
            ],
        ];

        DB::table('oauth_clients')->insert($clients);

        //2. Create Scopes

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

        //3. Create Grants

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

        //4. Specify which grants can be used by which scopes

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

        //5. Specify which scopes can be used by which clients

        $oauth_client_scopes = [
            [
                'client_id' => $web_client_id,
                'scope_id' => 'basic',
                'created_at' => $datetime,
                'updated_at' => $datetime,
            ],
            [
                'client_id' => $admin_web_client_id,
                'scope_id' => 'admin',
                'created_at' => $datetime,
                'updated_at' => $datetime,
            ],
            [
                'client_id' => $lambda_processor_client_id,
                'scope_id' => 'processor',
                'created_at' => $datetime,
                'updated_at' => $datetime,
            ]
        ];

        DB::table('oauth_client_scopes')->insert($oauth_client_scopes);

        //6. Specify which grants can be used by which clients

        $oauth_client_grants = [
            [
                'client_id' => $web_client_id,
                'grant_id' => 'password',
                'created_at' => $datetime,
                'updated_at' => $datetime,
            ],
            [
                'client_id' => $web_client_id,
                'grant_id' => 'refresh_token',
                'created_at' => $datetime,
                'updated_at' => $datetime,
            ],
            [
                'client_id' => $admin_web_client_id,
                'grant_id' => 'password_admin',
                'created_at' => $datetime,
                'updated_at' => $datetime,
            ],
            [
                'client_id' => $lambda_processor_client_id,
                'grant_id' => 'client_credentials',
                'created_at' => $datetime,
                'updated_at' => $datetime,
            ],
        ];

        DB::table('oauth_client_grants')->insert($oauth_client_grants);

        //7. Create a session with access and refresh tokens for generated users and processor

        $sessions = [
            [
                'id' => 1,
                'client_id' => $web_client_id,
                'owner_id'  => 1,
                'owner_type' => 'user',
                'created_at' => $datetime,
                'updated_at' => $datetime,
            ],
            [
                'id' => 2,
                'client_id' => $admin_web_client_id,
                'owner_id'  => 2,
                'owner_type' => 'user',
                'created_at' => $datetime,
                'updated_at' => $datetime,
            ],
            [
                'id' => 3,
                'client_id' => $lambda_processor_client_id,
                'owner_id'  => 0,
                'owner_type' => 'client',
                'created_at' => $datetime,
                'updated_at' => $datetime,
            ]
        ];

        DB::table('oauth_sessions')->insert($sessions);

        $tokens = [
            [
                'id' => $web_client_access_token,
                'session_id'  => 1,
                'expire_time' => 9999999999,//Should be 60
                'created_at' => $datetime,
                'updated_at' => $datetime,
            ],
            [
                'id' => $admin_web_client_access_token,
                'session_id'  => 2,
                'expire_time' => 9999999999,//Should be 60
                'created_at' => $datetime,
                'updated_at' => $datetime,
            ],
            [
                'id' => $lambda_processor_access_token,
                'session_id'  => 3,
                'expire_time' => 9999999999,//Should be 60
                'created_at' => $datetime,
                'updated_at' => $datetime,
            ]
        ];

        DB::table('oauth_access_tokens')->insert($tokens);

        $refreshtokens = [
            [
                'id' => $web_client_refresh_token,
                'access_token_id' => $web_client_access_token,
                'expire_time' => 9999999999,
                'created_at' => $datetime,
                'updated_at' => $datetime,
            ],
            [
                'id' => $admin_web_client_refresh_token,
                'access_token_id' => $admin_web_client_access_token,
                'expire_time' => 9999999999,
                'created_at' => $datetime,
                'updated_at' => $datetime,
            ]
        ];

        DB::table('oauth_refresh_tokens')->insert($refreshtokens);

        $token_scopes = [
            [
                'access_token_id' => $web_client_access_token,
                'scope_id' => 'basic',
                'created_at' => $datetime,
                'updated_at' => $datetime,
            ],
            [
                'access_token_id' => $admin_web_client_access_token,
                'scope_id' => 'admin',
                'created_at' => $datetime,
                'updated_at' => $datetime,
            ],
            [
                'access_token_id' => $lambda_processor_access_token,
                'scope_id' => 'processor',
                'created_at' => $datetime,
                'updated_at' => $datetime,
            ],
        ];

        DB::table('oauth_access_token_scopes')->insert($token_scopes);

    }
}
