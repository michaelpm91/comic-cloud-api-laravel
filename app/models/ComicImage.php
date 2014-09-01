<?php

class ComicImage extends \Eloquent {

	// Add your validation rules here
	public static $rules = [
		// 'title' => 'required'
	];

	// Don't forget to fill this array
	protected $fillable = [];

    public function collections(){
        //return $this->belongsTo('Collection');
        return $this->belongsToMany('Collection');
    }

}