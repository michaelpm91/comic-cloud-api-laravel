<?php namespace App;

use Illuminate\Database\Eloquent\Model;

class ComicBookArchive extends Model {

    public function upload(){
        return $this->belongsTo('Upload');
    }
    public function comicimages(){
        return $this->belongsToMany('App\ComicImage');
    }
    public function comics()
    {
        return $this->hasMany('App\Comic');
    }

}
