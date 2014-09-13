<?php

use Illuminate\Database\Eloquent\SoftDeletingTrait;

class Comic extends \Eloquent {

    use SoftDeletingTrait;

	// Add your validation rules here
	public static $rules = [
		// 'title' => 'required'
	];

    protected $dates = ['deleted_at'];

    private $image_url = '/api/v1/image/'; //todo-mike: find a more suitable location or way for this url.

	// Don't forget to fill this array
	protected $fillable = ['comic_issue','comic_writer','comic_collection'];

    protected $hidden = array('created_at', 'updated_at', 'user_id', 'series_id','collection_id','deleted_at');

    public function series()
    {
        return $this->belongsTo('Series');
    }
    public function user()
    {
        return $this->belongsTo('User');
	}
	public function getComicCollectionAttribute($json_array){
        $json_array = json_decode($json_array);

        array_walk($json_array, function(&$value, $key){
            $value = $this->image_url.$value;
        });

        /*foreach ($otherjson_array as $key => &$value)
            $value = $this->image_url.$value;*/

        return json_decode(json_encode($json_array, true));
	}
}
