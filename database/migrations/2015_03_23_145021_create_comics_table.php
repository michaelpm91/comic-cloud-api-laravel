<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateComicsTable extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::create('comics', function(Blueprint $table)
		{
            $table->string('id', 40)->primary();
            $table->integer('comic_issue');
            $table->string('comic_writer');
            $table->text('comic_book_archive_contents');
            $table->integer('user_id')->length(10)->unsigned();
            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
            $table->string('series_id', 40);
            $table->foreign('series_id')->references('id')->on('series')->onDelete('cascade');
            $table->integer('comic_book_archive_id')->length(10)->unsigned();
            $table->foreign('comic_book_archive_id')->references('id')->on('comic_book_archives')->onDelete('cascade');
            $table->integer('comic_status');
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
		Schema::drop('comics');
	}

}
