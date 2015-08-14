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
        $web_client_id = "SBziat92Is6qqShG";
        $web_client_secret = "dVPoCStWKNuAgsZagS21lqTKklpbVF8z";

        $admin_web_client_id = "PJQG0e3tOKWibQAS";
        $admin_web_client_secret = "WDOMm55MIsz4DoExTEnpyuZ1Nq6khZLN";

        $lambda_processor_client_id = "r9kO96j16pDdmQf9";
        $lambda_processor_client_secret = "jeeSHlMdKO1wHhVtGzCmUwMaH0CbzJRy";

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


    }
}
