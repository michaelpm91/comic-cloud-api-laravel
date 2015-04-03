<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateUploadsTable extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
        Schema::create('uploads', function(Blueprint $table)
        {
            $table->increments('id');
            $table->integer('user_id')->length(10)->unsigned();
            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
            $table->string('file_original_name');
            $table->string('file_original_file_type');
            $table->integer('file_size');
            $table->string('file_upload_name');
            $table->string('file_random_upload_id');
            $table->text('match_data');
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
        Schema::drop('uploads');
	}

}
