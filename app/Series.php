<?php namespace App;

use Illuminate\Database\Eloquent\Model;

class Series extends Model {

    public static $rules = [
        'id' => 'required'
    ];

    public $incrementing = false;

    protected $hidden = ['created_at', 'updated_at', 'user_id'];
    // Don't forget to fill this array
    protected $fillable = ['id', 'series_title', 'series_start_year', 'series_publisher'];

    public function comics(){
        return $this->hasMany('App\Comic')->orderBy('comic_issue', 'ASC');
    }

    public function user(){
        return $this->belongsTo('App\User');
    }

}
