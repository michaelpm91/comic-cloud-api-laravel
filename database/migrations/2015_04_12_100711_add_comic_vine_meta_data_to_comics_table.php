<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddComicVineMetaDataToComicsTable extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::table('comics', function(Blueprint $table)
		{
            $table->integer('comic_vine_issue_id')->nullable()->after('series_id');
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
            $table->dropColumn('comic_vine_issue_id');
        });
	}

}
