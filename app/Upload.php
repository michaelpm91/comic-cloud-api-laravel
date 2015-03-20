<?php namespace App;

use Illuminate\Database\Eloquent\Model;

class Upload extends Model {

	//
    public function user()
    {
        return $this->belongsTo('User');
    }

}
