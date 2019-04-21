<?php

namespace App;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;

class Pack extends Model
{
    protected $table = 'packs';
    protected $guarded = ['id'];
    protected $appends = ['numeracion', 'fecha_human'];

    public function forInput()
    {
        return [
            'id' => $this->id,
            'fecha' => $this->fecha_human,
            'lote' => $this->lote,
            'numeracion' => $this->numeracion,
            'fardos' => $this->fardos,
            'b' => $this->b, 'b14' => $this->b14, 'b12' => $this->b12, 'b34' => $this->b34,
            'c' => $this->c, 'c14' => $this->c14, 'c12' => $this->c12, 'c34' => $this->c34,
            'd' => $this->d, 'd14' => $this->d14, 'd12' => $this->d12, 'd34' => $this->d34,
            'e' => $this->e, 'e14' => $this->e14, 'e12' => $this->e12, 'e34' => $this->e34,
            'f' => $this->f, 'f14' => $this->f14, 'f12' => $this->f12, 'f34' => $this->f34,
            'micro' => $this->micro,
            'fibra' => $this->fibra,
            'notas' => $this->notas,
            'color_id' => $this->color_id,
            'vendedor_id' => $this->vendedor_id,
            'color' => $this->color_id ? $this->color : (object)[
                'id' => null,
                'color_code' => null,
                'nombre' => null
            ],
            'promedio' => '',
        ];
    }

    /**
    **********************************
    RELATIONSHIPS
    **********************************
    **/
    public function color ()
    {
        return $this->belongsTo(Color::class, 'color_id')->withDefault();
    }
    public function vendedor ()
    {
        return $this->belongsTo(Vendedor::class, 'vendedor_id');
    }
    /**
    **********************************
    SCOPES
    **********************************
    **/
    public function scopeInbox ( $query )
    {
        return $query->where(['status' => 0]);
    }
    public function scopeStock ( $query )
    {
        return $query->where(['status' => 1]);
    }
    public function scopeAvailable ( $query ) {
        return $query->where(['status' => 2])
            ->orWhere(['status' => 1]);
    }
    /**
    **********************************
    ATTRIBUTES
    **********************************
    **/
    public function getFechaHumanAttribute ()
    {
        if ( $this->fecha != '') {
            return Carbon::createFromFormat('Y-m-d', $this->fecha)->format('d/m/Y');
        }
        else
        {
            return '';
        }
    }
    /*public function getFardosAttribute ()
    {
        return (int)$this->b + (int)$this->b14 + (int)$this->b12 + (int)$this->b34 +
            (int)$this->c + (int)$this->c14 + (int)$this->c12 + (int)$this->c34 +
            (int)$this->d + (int)$this->d14 + (int)$this->d12 + (int)$this->d34 +
            (int)$this->e + (int)$this->e14 + (int)$this->e12 + (int)$this->e34 +
            (int)$this->f + (int)$this->f14 + (int)$this->f12 + (int)$this->f34;
    }*/
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
