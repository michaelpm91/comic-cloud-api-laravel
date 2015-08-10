<?php namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ComicBookArchive extends Model {

    protected $hidden = array('updated_at', 'deleted_at');

    public function upload(){
        return $this->belongsTo('Upload');
    }
    public function comicimages(){
        return $this->belongsToMany('App\Models\ComicImage');
    }
    public function comics()
    {
        return $this->hasMany('App\Models\Comic');
    }
    public function getComicBookArchiveContentsAttribute($json_array){
        if($json_array) {
            $json_array = json_decode($json_array);

            array_walk($json_array, function (&$value, $key) {
                $value = url('v'.env('APP_API_VERSION').env('image_url') . $value);
            });
            return json_decode(json_encode($json_array, true));
        }
    }

}
