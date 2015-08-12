<?php namespace App\Models\Admin;

use Illuminate\Database\Eloquent\Model;

class Series extends Model {

    public static $rules = [
        'id' => 'required|alpha_num|min:40|max:40'
    ];

    public $incrementing = false;

    protected $hidden = [];

    protected $fillable = ['id', 'series_title', 'series_start_year', 'series_publisher'];

    protected $appends = ['series_cover_img'];

    public function comics(){
        return $this->hasMany('App\Models\Admin\Comic')->orderBy('comic_issue', 'ASC');
    }

    public function user(){
        return $this->belongsTo('App\Models\Admin\User');
    }

    public function getSeriesCoverImgAttribute(){
        $first_comic = $this->comics()->get()->first()['comic_book_archive_contents'];
        if(!$first_comic) return null;
        return head($first_comic);
    }

}
