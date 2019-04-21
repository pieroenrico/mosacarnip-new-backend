<?php

namespace App;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;

class Lote extends Model
{
    protected $table = 'lotes';
    protected $guarded = ['id'];
    protected $appends = ['code', 'fecha_human', 'numeracion'];

    /**
    **********************************
    RELATIONSHIPS
    **********************************
    **/
    public function lotpacks () {
        return $this->hasMany(Lotpack::class, 'lote_id');
    }
    public function history () {
        return $this->hasMany(LotpackHistory::class, 'lote_id')->where(['partial' => 0]);
    }
    public function history_full () {
        return $this->hasMany(LotpackHistory::class, 'lote_id');
    }
    public function vendedor () {
        return $this->belongsTo(Vendedor::class, 'vendedor_id');
    }
    public function remito () {
        return $this->belongsTo(Remito::class, 'remito_id');
    }
    /**
    **********************************
    SCOPES
    **********************************
    **/
    public function scopePure ( $query )
    {
        return $query->where(['remito_id' => null]);
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
        return $this->fecha ? Carbon::createFromFormat('Y-m-d', $this->fecha)->format('d/m/Y') : '';
    }
    public function getNumeracionAttribute ()
    {
        if ( $this->num_start && $this->num_end )
        {
            return $this->num_start . '/' . $this->num_end;
        }
        else
        {
            return null;
        }
    }
}
