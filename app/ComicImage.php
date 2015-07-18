<?php namespace App;

use Illuminate\Database\Eloquent\Model;

class ComicImage extends Model {

    protected $fillable = [];

    protected $guarded = ['updated_at', 'created_at'];

    protected $hidden = array('id', 'updated_at');

    public function comicBookArchives(){
        return $this->belongsToMany('App\ComicBookArchive')->withTimestamps();
    }
}
