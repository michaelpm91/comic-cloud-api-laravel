<?php namespace App\Models\Admin;

use Illuminate\Database\Eloquent\Model;

class Upload extends Model {

    protected $fillable = [];

    protected $table = "uploads";

    protected $guarded = ['updated_at', 'created_at'];

    protected $hidden = array();

	//
    public function user()
    {
        return $this->belongsTo('App\AdminUser');
    }

    public function ComicBookArchives()
    {
        return $this->hasMany('App\ComicBookArchive');
    }

    public function getMatchDataAttribute($json_array){
        if($json_array) {
            return json_decode($json_array);
        }
    }

}
