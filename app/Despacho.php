<?php

namespace App;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;

class Despacho extends Model
{
    protected $table = 'despachos';
    protected $guarded = ['id'];
    protected $appends = ['code', 'fecha_human'];


    /**
    **********************************
    RELATIONSHIPS
    **********************************
    **/
    public function remito()
    {
        return $this->belongsTo(Remito::class, 'remito_id');
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
    public function getFechaHumanAttribute () {
        return $this->created_at ? Carbon::createFromFormat('Y-m-d H:i:s', $this->created_at)->format('d/m/Y') : '';
    }
}
