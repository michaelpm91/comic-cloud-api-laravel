<?php namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ComicImage extends Model {

    protected $fillable = [];

    protected $guarded = ['updated_at', 'created_at'];

    protected $hidden = array('updated_at');

    public function comicBookArchives(){
        return $this->belongsToMany('App\Models\ComicBookArchive')->withTimestamps();
    }
}
