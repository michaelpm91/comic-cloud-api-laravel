<?php

class Collection extends \Eloquent {

	// Add your validation rules here
	public static $rules = [
		// 'title' => 'required'
	];

	// Don't forget to fill this array
	protected $fillable = [];

    public function upload()
    {
        return $this->belongsTo('Upload');
    }
    public function comicimages(){
        //return $this->hasMany('ComicImage');
        return $this->belongsToMany('ComicImage');
    }


}