<?php

class Upload extends \Eloquent {

	// Add your validation rules here
	public static $rules = [
		// 'title' => 'required'
	];

	// Don't forget to fill this array
	protected $fillable = [];

    public function user()
    {
        return $this->belongsTo('User');
    }

    public function collections()
    {
        return $this->hasMany('Collection');
    }


}