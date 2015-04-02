<?php

return [

	/*
	|--------------------------------------------------------------------------
	| Default Filesystem Disk
	|--------------------------------------------------------------------------
	|
	| Here you may specify the default filesystem disk that should be used
	| by the framework. A "local" driver, as well as a variety of cloud
	| based drivers are available for your choosing. Just store away!
	|
	| Supported: "local", "s3", "rackspace"
	|
	*/

	'default' => 'local',

	/*
	|--------------------------------------------------------------------------
	| Default Cloud Filesystem Disk
	|--------------------------------------------------------------------------
	|
	| Many applications store files both locally and in the cloud. For this
	| reason, you may specify a default "cloud" driver here. This driver
	| will be bound as the Cloud disk implementation in the container.
	|
	*/

	'cloud' => 's3',

	/*
	|--------------------------------------------------------------------------
	| Filesystem Disks
	|--------------------------------------------------------------------------
	|
	| Here you may configure as many filesystem "disks" as you wish, and you
	| may even configure multiple disks of the same driver. Defaults have
	| been setup for each driver as an example of the required options.
	|
	*/

	'disks' => [

		'local_user_uploads' => [
			'driver' => 'local',
			'root'   => storage_path().'/app/user_uploads',
		],
        'local_user_images' => [
            'driver' => 'local',
            'root'   => storage_path().'/app/user_images',
        ],
        'local_cba_extraction_area' => [
            'driver' => 'local',
            'root'   => storage_path().'/app/comic_book_archive_extraction_area',
        ],
        'aws_s3_user_uploads' => [
            'driver' => 's3',
            'key'    => env('AWS_Key'),
            'secret' => env('AWS_Secret'),
            'region' => env('AWS_S3_Region'),
            'bucket' => env('AWS_S3_Uploads'),
        ],
        'aws_s3_user_images' => [
            'driver' => 's3',
            'key'    => env('AWS_Key'),
            'secret' => env('AWS_Secret'),
            'region' => env('AWS_S3_Region'),
            'bucket' => env('AWS_S3_Images'),
        ],
	],

];
