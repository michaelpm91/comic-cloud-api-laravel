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
    }
}
