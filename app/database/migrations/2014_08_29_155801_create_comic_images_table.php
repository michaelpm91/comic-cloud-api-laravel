<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

class CreateComicImagesTable extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::create('comic_images', function(Blueprint $table)
		{
			$table->increments('id');
            $table->string('image_set_key');
            $table->string('image_size');
            $table->string('image_hash');
            $table->string('image_url');
            $table->integer('collection_id')->length(10)->unsigned();
            $table->foreign('collection_id')->references('id')->on('collections')->onDelete('cascade');
			$table->timestamps();
		});
	}


	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down()
	{
		Schema::drop('comic_images');
	}

}
