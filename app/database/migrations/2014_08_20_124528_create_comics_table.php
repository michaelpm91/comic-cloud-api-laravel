<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

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
            $table->text('comic_collection');
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
