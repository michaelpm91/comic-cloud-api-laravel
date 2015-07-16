<?php

use Illuminate\Database\Schema\Blueprint;
use LucaDegasperi\OAuth2Server\Support\Migration;

class CreateOauthSessionsTable extends Migration
{

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        $this->schema()->create('oauth_sessions', function (Blueprint $table) {
            $table->increments('id');
            $table->string('client_id', 40);
            $table->enum('owner_type', ['client', 'user'])->default('user');
            $table->integer('owner_id')->length(10)->unsigned();
            $table->string('client_redirect_uri')->nullable();
            $table->timestamps();

            $table->index(['client_id', 'owner_type', 'owner_id']);

            $table->foreign('client_id')
                ->references('id')->on('oauth_clients')
                ->onDelete('cascade')
                ->onUpdate('cascade');

            /*$table->foreign('owner_id')
                ->references('id')->on('users')
                ->onDelete('cascade');*/
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        $this->schema()->table('oauth_sessions', function (Blueprint $table) {
            $table->dropForeign('oauth_sessions_client_id_foreign');
        });
        $this->schema()->drop('oauth_sessions');
    }
}
