<?php namespace App;

use Illuminate\Database\Eloquent\Model;

class ComicImage extends Model {

    public $incrementing = false;

    public function comicBookArchives(){
        return $this->belongsToMany('App\ComicBookArchive')->withTimestamps();
    }
}
