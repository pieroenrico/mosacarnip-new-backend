<?php

namespace App;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;

class LotpackHistory extends Model
{
    protected $table = 'history_lotpacks';
    protected $guarded = ['id'];
    protected $appends = ['fecha_human', 'numeracion'];

    /**
    **********************************
    RELATIONSHIPS
    **********************************
    **/
    public function numeraciones ()
    {
        return $this->hasMany(NumeracionHistory::class, 'lotpack_id')->orderBy('sort_order');
    }
    public function color ()
    {
        return $this->belongsTo(Color::class, 'color_id');
    }
    public function vendedor ()
    {
        return $this->belongsTo(Vendedor::class, 'vendedor_id');
    }
    public function lote_detail () {
        return $this->belongsTo(Lote::class, 'lote_id');
    }

    /**
    **********************************
    ATTRIBUTES
    **********************************
    **/
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
