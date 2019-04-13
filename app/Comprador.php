<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Comprador extends Model
{
    protected $table = 'compradores';
    protected $guarded = ['id'];
    protected $appends = ['code'];


    /**
    **********************************
    RELATIONSHIPS
    **********************************
    **/
    public function provincia ()
    {
        return $this->belongsTo(Provincia::class, 'provincia_id');
    }
    public function localidad ()
    {
        return $this->belongsTo(Localidad::class, 'localidad_id');
    }
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
