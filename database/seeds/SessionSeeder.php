<?php
/**
 * Created by PhpStorm.
 * User: Michael
 * Date: 15/08/15
 * Time: 11:12
 */

use Illuminate\Database\Seeder;

class SessionSeeder extends Seeder {

    protected $web_client_id = "SBziat92Is6qqShG";
    protected $web_client_secret = "dVPoCStWKNuAgsZagS21lqTKklpbVF8z";
    protected $web_client_access_token = "y2ZRXZridqzVZP0mIzlaWBoQmLJplvqCcXmKOt4j";
    protected $web_client_refresh_token = "XyOLcDQmGGDWL2SsIcH6hhm1Z04ShC5LymYQmDeX";

    protected $admin_web_client_id = "PJQG0e3tOKWibQAS";
    protected $admin_web_client_secret = "WDOMm55MIsz4DoExTEnpyuZ1Nq6khZLN";
    protected $admin_web_client_access_token = "iw8yKb073hI0O8szPou8ZliIlvzLHS9sPrT4WmmJ";
    protected $admin_web_client_refresh_token = "2u8YeRKtgFlf3bptxrLRwFsBQCZrCvPyxRHcRIs7";

    protected $lambda_processor_client_id = "r9kO96j16pDdmQf9";
    protected $lambda_processor_client_secret = "jeeSHlMdKO1wHhVtGzCmUwMaH0CbzJRy";
    protected $lambda_processor_access_token = "m7wQwuDdCq2FQvW2tjzALUnVc0KZe2YogLaxSOA6";
    protected $lambda_processor_refresh_token = "T6XaStdCiaSmeQzDdm1b616WGNIb60YXxdv0xAK1";


    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $datetime = Carbon::now();

        //1. Create a session with access and refresh tokens for generated users and processor
        $sessions = [
            [
                'id' => 1,
                'client_id' => $this->web_client_id,
                'owner_id' => 1,
                'owner_type' => 'user',
                'created_at' => $datetime,
                'updated_at' => $datetime,
            ],
            [
                'id' => 2,
                'client_id' => $this->admin_web_client_id,
                'owner_id' => 2,
                'owner_type' => 'user',
                'created_at' => $datetime,
                'updated_at' => $datetime,
            ],
            [
                'id' => 3,
                'client_id' => $this->lambda_processor_client_id,
                'owner_id' => 0,
                'owner_type' => 'client',
                'created_at' => $datetime,
                'updated_at' => $datetime,
            ]
        ];
        DB::table('oauth_sessions')->insert($sessions);

        $tokens = [
            [
                'id' => $this->web_client_access_token,
                'session_id' => 1,
                'expire_time' => 9999999999,//Should be 60
                'created_at' => $datetime,
                'updated_at' => $datetime,
            ],
            [
                'id' => $this->admin_web_client_access_token,
                'session_id' => 2,
                'expire_time' => 9999999999,//Should be 60
                'created_at' => $datetime,
                'updated_at' => $datetime,
            ],
            [
                'id' => $this->lambda_processor_access_token,
                'session_id' => 3,
                'expire_time' => 9999999999,//Should be 60
                'created_at' => $datetime,
                'updated_at' => $datetime,
            ]
        ];
        DB::table('oauth_access_tokens')->insert($tokens);
        $refreshtokens = [
            [
                'id' => $this->web_client_refresh_token,
                'access_token_id' => $this->web_client_access_token,
                'expire_time' => 9999999999,
                'created_at' => $datetime,
                'updated_at' => $datetime,
            ],
            [
                'id' => $this->admin_web_client_refresh_token,
                'access_token_id' => $this->admin_web_client_access_token,
                'expire_time' => 9999999999,
                'created_at' => $datetime,
                'updated_at' => $datetime,
            ]
        ];
        DB::table('oauth_refresh_tokens')->insert($refreshtokens);
        $token_scopes = [
            [
                'access_token_id' => $this->web_client_access_token,
                'scope_id' => 'basic',
                'created_at' => $datetime,
                'updated_at' => $datetime,
            ],
            [
                'access_token_id' => $this->admin_web_client_access_token,
                'scope_id' => 'admin',
                'created_at' => $datetime,
                'updated_at' => $datetime,
            ],
            [
                'access_token_id' => $this->lambda_processor_access_token,
                'scope_id' => 'processor',
                'created_at' => $datetime,
                'updated_at' => $datetime,
            ],
        ];
        DB::table('oauth_access_token_scopes')->insert($token_scopes);
    }

}