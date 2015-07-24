<?php namespace App;

use Illuminate\Database\Eloquent\Model;

class Comic extends Model {

    public static $rules = [
        'id' => 'required|alpha_num|min:40|max:40'
    ];

    public $incrementing = false;

    protected $dates = ['deleted_at'];

    protected $fillable = ['comic_issue','comic_writer','comic_book_archive_contents'];

    protected $hidden = array('created_at', 'updated_at', 'user_id','comic_book_archive_id','deleted_at');

    protected $appends = ['from_cba'];

    public function series()
    {
        return $this->belongsTo('App\Series');
    }
    public function user()
    {
        return $this->belongsTo('App\User');
    }
    public function cba(){
        return $this->belongsTo('App\ComicBookArchive');
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

    public function getFromCbaAttribute(){
        //dd($this->cba()->id);
        dd($this->cba()->get());
        return $this->cba();
        //return "1";
    }

}
