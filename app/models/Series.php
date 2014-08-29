<?php

class Series extends \Eloquent {
	
	// Add your validation rules here
	public static $rules = [
		// 'title' => 'required'
	];
	protected $hidden = ['created_at', 'updated_at', 'user_id'];
	// Don't forget to fill this array
	protected $fillable = [];
	
	public function comics(){
		return $this->hasMany('Comic');
	}
	public function user(){
		return $this->belongsTo('User');
	}
}
