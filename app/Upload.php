<?php namespace App;

use Illuminate\Database\Eloquent\Model;

class Upload extends Model {

    protected $fillable = [];

    protected $guarded = ['updated_at', 'created_at'];

	//
    public function user()
    {
        return $this->belongsTo('User');
    }

}
