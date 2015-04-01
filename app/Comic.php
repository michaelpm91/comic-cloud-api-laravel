<?php namespace App;

use Illuminate\Database\Eloquent\Model;

class Comic extends Model {

    public static $rules = [
        'id' => 'required'
    ];

    protected $dates = ['deleted_at'];

    protected $fillable = ['comic_issue','comic_writer','comic_book_archive_contents'];

    protected $hidden = array('created_at', 'updated_at', 'user_id', 'series_id','comic_book_archive_id','deleted_at');

    public function series()
    {
        return $this->belongsTo('Series');
    }
    public function user()
    {
        return $this->belongsTo('User');
    }
    public function getComicBookArchiveContentsAttribute($json_array){
        if($json_array) {
            $json_array = json_decode($json_array);

            array_walk($json_array, function (&$value, $key) {
                $value = env('image_url') . $value;
            });
            return json_decode(json_encode($json_array, true));
        }
    }

}
