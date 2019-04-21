<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Color extends Model
{
    protected $table = 'colores';
    protected $guarded = ['id'];
    protected $appends = ['code'];

    /**
    **********************************
    ATTRIBUTES
    **********************************
    **/
    public function getCodeAttribute ()
    {
        if (array_key_exists('id', $this->attributes))
        {
            return str_pad($this->attributes['id'], config('mosa.code_pad'), '0', STR_PAD_LEFT);
        }
        else
        {
            return null;
        }
    }
}
