<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddComicVineMetaDataToSeriesTable extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::table('series', function(Blueprint $table)
		{
            $table->integer('comic_vine_series_id')->nullable()->after('series_publisher');
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
            $table->dropColumn('comic_vine_series_id');
		});
	}

}
