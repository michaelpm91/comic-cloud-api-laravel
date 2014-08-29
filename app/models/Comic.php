<?php

use Illuminate\Database\Eloquent\SoftDeletingTrait;

class Comic extends \Eloquent {

    use SoftDeletingTrait;

	// Add your validation rules here
	public static $rules = [
		// 'title' => 'required'
	];

    protected $dates = ['deleted_at'];

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
	public function getComicCollectionAttribute($value){
		return json_decode($value);
	}
}
