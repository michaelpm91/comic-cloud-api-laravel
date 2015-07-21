<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddImageUrlToComicImagesTable extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::table('comic_images', function(Blueprint $table)
		{
            $table->text('image_url')->nullable()->after('image_size');
		});
	}

	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down()
	{
		Schema::table('comic_images', function(Blueprint $table)
		{
            $table->dropColumn('image_url');
        });
	}

}
