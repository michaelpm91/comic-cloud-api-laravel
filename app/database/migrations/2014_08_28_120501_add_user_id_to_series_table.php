<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

class AddUserIdToSeriesTable extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::table('series', function(Blueprint $table)
		{
			 $table->integer('user_id')->length(10)->unsigned()->after('series_publisher');
			 $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
		});
	}


	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down()
	{
		Schema::table('series', function(Blueprint $table)
		{
				$table->dropForeign('series_user_id_foreign');				
		});
	}

}
