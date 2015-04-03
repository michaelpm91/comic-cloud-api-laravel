<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateComicBookArchiveComicImageTable extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
        Schema::create('comic_book_archive_comic_image', function(Blueprint $table)
        {
            $table->increments('id');
            $table->integer('comic_book_archive_id')->unsigned()->index();
            $table->foreign('comic_book_archive_id')->references('id')->on('comic_book_archives')->onDelete('cascade');
            $table->integer('comic_image_id')->unsigned()->index();
            $table->foreign('comic_image_id')->references('id')->on('comic_images')->onDelete('cascade');
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
        Schema::drop('comic_book_archive_comic_image');
	}

}
