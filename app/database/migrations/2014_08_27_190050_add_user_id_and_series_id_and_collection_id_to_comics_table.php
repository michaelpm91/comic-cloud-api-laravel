<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

class AddUserIdAndSeriesIdAndCollectionIdToComicsTable extends Migration{
	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::table('comics', function(Blueprint $table)
		{
            $table->integer('user_id')->length(10)->unsigned()->after('comic_collection');
            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
            $table->string('series_id', 40)->after('user_id');
            $table->foreign('series_id')->references('id')->on('series')->onDelete('cascade');
            $table->integer('collection_id')->length(10)->unsigned()->after('series_id');
            $table->foreign('collection_id')->references('id')->on('collections')->onDelete('cascade');
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
			$table->dropForeign('comics_user_id_foreign');
			$table->dropForeign('comics_series_id_foreign');
            $table->dropForeign('comics_collection_id_foreign');
		});
	}

}
