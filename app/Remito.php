<?php

namespace App;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;

class Remito extends Model
{
    protected $table = 'remitos';
    protected $guarded = ['id'];
    protected $appends = ['code', 'fecha_human', 'lotpacks'];

    /**
    **********************************
    RELATIONSHIPS
    **********************************
    **/
    public function vendedor () {
        return $this->belongsTo(Vendedor::class, 'vendedor_id');
    }
    public function comprador () {
        return $this->belongsTo(Comprador::class, 'comprador_id');
    }
    public function lote () {
        return $this->hasOne(Lote::class, 'remito_id');
    }
    /**
    **********************************
    ATTRIBUTES
    **********************************
    **/
    public function getIsArchivedAttribute () {
        return $this->status == 3;
    }

    public function getLotpacksAttribute () {
        if ( $this->status == 3 )
        {
            return $this->lote()->first()->history()->get();
        }
        else
        {
            return $this->lote()->first()->lotpacks()->get();
        }
    }
    public function getCodeAttribute ()
    {
        return str_pad($this->attributes['id'], config('mosa.code_pad'), '0', STR_PAD_LEFT);
    }
    public function getFechaHumanAttribute () {
        return $this->fecha ? Carbon::createFromFormat('Y-m-d', $this->fecha)->format('d/m/Y') : '';
    }
}
