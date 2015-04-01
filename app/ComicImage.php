<?php namespace App;

use Illuminate\Database\Eloquent\Model;

class ComicImage extends Model {

    public function comicBookArchives(){
        return $this->belongsToMany('ComicBookArchive')->withTimestamps();
    }
}
