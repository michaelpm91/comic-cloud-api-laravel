<?php namespace App;

use Illuminate\Database\Eloquent\Model;

class Upload extends Model {

    protected $fillable = [];

    protected $guarded = ['updated_at', 'created_at'];

    protected $hidden = array('id', 'user_id', 'file_upload_name', 'updated_at');

	//
    public function user()
    {
        return $this->belongsTo('User');
    }

}
