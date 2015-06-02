<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class ChangeCacheValueToLongText extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::table('cache', function(Blueprint $table)
		{
            DB::statement('ALTER TABLE cache MODIFY COLUMN value LONGTEXT NOT NULL');

        });
	}

	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down()
	{
		Schema::table('cache', function(Blueprint $table)
		{
            DB::statement('ALTER TABLE cache MODIFY COLUMN value TEXT NOT NULL');
		});
	}

}
