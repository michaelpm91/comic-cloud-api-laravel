<?php namespace App\Models\Admin;

use Illuminate\Database\Eloquent\Model;

class ComicBookArchive extends Model {

    protected $hidden = array();

    public function upload(){
        return $this->belongsTo('Upload');
    }
    public function comicimages(){
        return $this->belongsToMany('App\Models\Admin\ComicImage');
    }
    public function comics()
    {
        return $this->hasMany('App\Models\Admin\Comic');
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
