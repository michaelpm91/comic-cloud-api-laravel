<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

class AddUserIdToUploadsTable extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::table('uploads', function(Blueprint $table)
		{
            $table->foreign('user_id')->references('id')->on('users');
            /*$table->integer('user_id')->length(10)->unsigned()
                ->after('id')
                ->foreign('user_id')
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
		Schema::table('uploads', function(Blueprint $table)
		{
            $table->dropForeign('uploads_user_id_foreign');
            $table->dropColumn('user_id');
            //$table->dropForeign('uploads_user_id_foreign');
		});
	}

}
