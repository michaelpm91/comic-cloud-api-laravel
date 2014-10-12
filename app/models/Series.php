<?php

class Series extends \Eloquent {
	
	// Add your validation rules here
	public static $rules = [
		// 'title' => 'required'
        'id' => 'required'
	];
	protected $hidden = ['created_at', 'updated_at', 'user_id'];
	// Don't forget to fill this array
	protected $fillable = ['id', 'series_title', 'series_start_year', 'series_publisher'];
	
	public function comics(){
		return $this->hasMany('Comic')->orderBy('comic_issue', 'ASC');
	}
	public function user(){
		return $this->belongsTo('User');
	}
}
