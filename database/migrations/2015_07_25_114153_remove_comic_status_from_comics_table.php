<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class RemoveComicStatusFromComicsTable extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::table('comics', function(Blueprint $table)
		{
            $table->dropColumn('comic_status');
		});
	}

	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down()
	{
		Schema::table('comics', function(Blueprint $table)
		{
            $table->integer('comic_status')->default(0);
        });
	}

}
