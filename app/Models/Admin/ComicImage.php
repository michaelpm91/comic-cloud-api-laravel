<?php namespace App\Models\Admin;

use Illuminate\Database\Eloquent\Model;

class ComicImage extends Model {

    protected $fillable = [];

    protected $table = "comic_images";

    protected $guarded = ['updated_at', 'created_at'];

    protected $hidden = array();

    public function comicBookArchives(){
        return $this->belongsToMany('App\Models\Admin\ComicBookArchive')->withTimestamps();
    }
}
