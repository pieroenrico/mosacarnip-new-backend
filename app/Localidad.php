<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Localidad extends Model
{
    protected $table = 'localidades';
    protected $guarded = ['id'];
    protected $appends = ['code'];


    /**
     **********************************
    ATTRIBUTES
     **********************************
     **/
    public function getCodeAttribute ()
    {
        return str_pad($this->attributes['id'], config('mosa.code_pad'), '0', STR_PAD_LEFT);
    }
}
