<?php namespace App;

use Illuminate\Database\Eloquent\Model;

class Upload extends Model {

    protected $fillable = [];

    protected $guarded = ['updated_at', 'created_at'];

    protected $hidden = array('user_id', 'file_upload_name', 'file_original_file_type', 'file_random_upload_id', 'updated_at');

	//
    public function user()
    {
        return $this->belongsTo('App\User');
    }

    public function ComicBookArchives()
    {
        return $this->hasMany('App\ComicBookArchive');
    }

}
