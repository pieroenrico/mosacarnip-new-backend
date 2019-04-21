<?
namespace App\Workers;

use App\Lote;
use App\Lotpack;
use App\LotpackHistory;
use App\Numeracion;
use App\NumeracionHistory;
use App\Pack;
use Carbon\Carbon;

class Packer {

    static function archive($lotpack, $is_partial = 0)
    {
        $lotpack_history = new LotpackHistory;
        $lotpack_history->lote_id = $lotpack->lote_id;
        $lotpack_history->parent_id = $lotpack->parent_id;
        $lotpack_history->fecha = $lotpack->fecha;
        $lotpack_history->lote = $lotpack->lote;
        $lotpack_history->num_start = $lotpack->num_start;
        $lotpack_history->num_end = $lotpack->num_end;
        $lotpack_history->fardos = $lotpack->fardos;
        $lotpack_history->b = (int)$lotpack->b == 0 ? null : $lotpack->b;
        $lotpack_history->b14 = (int)$lotpack->b14 == 0 ? null : $lotpack->b14;
        $lotpack_history->b12 = (int)$lotpack->b12 == 0 ? null : $lotpack->b12;
        $lotpack_history->b34 = (int)$lotpack->b34 == 0 ? null : $lotpack->b34;
        $lotpack_history->c = (int)$lotpack->c == 0 ? null : $lotpack->c;
        $lotpack_history->c14 = (int)$lotpack->c14 == 0 ? null : $lotpack->c14;
        $lotpack_history->c12 = (int)$lotpack->c12 == 0 ? null : $lotpack->c12;
        $lotpack_history->c34 = (int)$lotpack->c34 == 0 ? null : $lotpack->c34;
        $lotpack_history->d = (int)$lotpack->d == 0 ? null : $lotpack->d;
        $lotpack_history->d14 = (int)$lotpack->d14 == 0 ? null : $lotpack->d14;
        $lotpack_history->d12 = (int)$lotpack->d12 == 0 ? null : $lotpack->d12;
        $lotpack_history->d34 = (int)$lotpack->d34 == 0 ? null : $lotpack->d34;
        $lotpack_history->e = (int)$lotpack->e == 0 ? null : $lotpack->e;
        $lotpack_history->e14 = (int)$lotpack->e14 == 0 ? null : $lotpack->e14;
        $lotpack_history->e12 = (int)$lotpack->e12 == 0 ? null : $lotpack->e12;
        $lotpack_history->e34 = (int)$lotpack->e34 == 0 ? null : $lotpack->e34;
        $lotpack_history->f = (int)$lotpack->f == 0 ? null : $lotpack->f;
        $lotpack_history->f14 = (int)$lotpack->f14 == 0 ? null : $lotpack->f14;
        $lotpack_history->f12 = (int)$lotpack->f12 == 0 ? null : $lotpack->f12;
        $lotpack_history->f34 = (int)$lotpack->f34 == 0 ? null : $lotpack->f34;
        $lotpack_history->micro = $lotpack->micro;
        $lotpack_history->fibra = $lotpack->fibra;
        $lotpack_history->color_id = $lotpack->color_id;
        $lotpack_history->notas = $lotpack->notas;
        $lotpack_history->vendedor_id = $lotpack->vendedor_id;
        $lotpack_history->partial = $is_partial;
        $lotpack_history->save();

        foreach ($lotpack->numeraciones as $numeracion)
        {
            $numeracion_history = new NumeracionHistory;
            $numeracion_history->lotpack_id = $lotpack_history->id;
            $numeracion_history->sort_order = $numeracion->sort_order;
            $numeracion_history->calidad = $numeracion->calidad;
            $numeracion_history->type = $numeracion->type;
            $numeracion_history->fardos = $numeracion->fardos;
            $numeracion_history->num_start = $numeracion->num_start;
            $numeracion_history->num_end = $numeracion->num_end;
            $numeracion_history->numeracion = $numeracion->numeracion;
            $numeracion_history->removed = $numeracion->removed ? $numeracion->removed : 0;
            $numeracion_history->save();
        }

    }

    static function unarchive($lotpack)
    {
        /***
         *
         * PONER UNA ALERTA CUANDO NO DESARCHIVA UN LOTE PORQUE ESTA FORMANDO PARTE DE OTRO LOTE
         *
         *
         */
        // Tengo que chequear que el lote todavía está disponible
        $pack_original = Pack::where(['id' => $lotpack->parent_id])->first();
        if ( $pack_original->status == 1 ) // está en stock y no está asignado a otro remito o lote
        {
            $lotpack_unarchive = new Lotpack;
            $lotpack_unarchive->lote_id = $lotpack->lote_id;
            $lotpack_unarchive->parent_id = $lotpack->parent_id;
            $lotpack_unarchive->fecha = $lotpack->fecha;
            $lotpack_unarchive->lote = $lotpack->lote;
            $lotpack_unarchive->num_start = $lotpack->num_start;
            $lotpack_unarchive->num_end = $lotpack->num_end;
            $lotpack_unarchive->fardos = $lotpack->fardos;
            $lotpack_unarchive->b = $lotpack->b;
            $lotpack_unarchive->b14 = $lotpack->b14;
            $lotpack_unarchive->b12 = $lotpack->b12;
            $lotpack_unarchive->b34 = $lotpack->b34;
            $lotpack_unarchive->c = $lotpack->c;
            $lotpack_unarchive->c14 = $lotpack->c14;
            $lotpack_unarchive->c12 = $lotpack->c12;
            $lotpack_unarchive->c34 = $lotpack->c34;
            $lotpack_unarchive->d = $lotpack->d;
            $lotpack_unarchive->d14 = $lotpack->d14;
            $lotpack_unarchive->d12 = $lotpack->d12;
            $lotpack_unarchive->d34 = $lotpack->d34;
            $lotpack_unarchive->e = $lotpack->e;
            $lotpack_unarchive->e14 = $lotpack->e14;
            $lotpack_unarchive->e12 = $lotpack->e12;
            $lotpack_unarchive->e34 = $lotpack->e34;
            $lotpack_unarchive->f = $lotpack->f;
            $lotpack_unarchive->f14 = $lotpack->f14;
            $lotpack_unarchive->f12 = $lotpack->f12;
            $lotpack_unarchive->f34 = $lotpack->f34;
            $lotpack_unarchive->micro = $lotpack->micro;
            $lotpack_unarchive->fibra = $lotpack->fibra;
            $lotpack_unarchive->color_id = $lotpack->color_id;
            $lotpack_unarchive->notas = $lotpack->notas;
            $lotpack_unarchive->vendedor_id = $lotpack->vendedor_id;
            $lotpack_unarchive->save();

            foreach ($lotpack->numeraciones as $numeracion)
            {
                $numeracion_unarchive = new Numeracion;
                $numeracion_unarchive->lotpack_id = $lotpack_unarchive->id;
                $numeracion_unarchive->sort_order = $numeracion->sort_order;
                $numeracion_unarchive->calidad = $numeracion->calidad;
                $numeracion_unarchive->type = $numeracion->type;
                $numeracion_unarchive->fardos = $numeracion->fardos;
                $numeracion_unarchive->num_start = $numeracion->num_start;
                $numeracion_unarchive->num_end = $numeracion->num_end;
                $numeracion_unarchive->numeracion = $numeracion->numeracion;
                $numeracion_unarchive->save();
            }

        }

    }

    static function unarchivePartial($lotpack)
    {
        /***
         *
         * PONER UNA ALERTA CUANDO NO DESARCHIVA UN LOTE PORQUE ESTA FORMANDO PARTE DE OTRO LOTE
         *
         *
         */
        // Tengo que chequear que el lote todavía está disponible
        $pack_original = Pack::where(['id' => $lotpack->parent_id])->first();
        if ( $pack_original->status == 1 ) // está en stock y no está asignado a otro remito o lote
        {
            $lotpack_unarchive = new Lotpack;
            $lotpack_unarchive->lote_id = $lotpack->lote_id;
            $lotpack_unarchive->parent_id = $lotpack->parent_id;
            $lotpack_unarchive->fecha = $lotpack->fecha;
            $lotpack_unarchive->lote = $lotpack->lote;
            $lotpack_unarchive->num_start = $lotpack->num_start;
            $lotpack_unarchive->num_end = $lotpack->num_end;
            $lotpack_unarchive->fardos = $lotpack->fardos;
            $lotpack_unarchive->b = $lotpack->b;
            $lotpack_unarchive->b14 = $lotpack->b14;
            $lotpack_unarchive->b12 = $lotpack->b12;
            $lotpack_unarchive->b34 = $lotpack->b34;
            $lotpack_unarchive->c = $lotpack->c;
            $lotpack_unarchive->c14 = $lotpack->c14;
            $lotpack_unarchive->c12 = $lotpack->c12;
            $lotpack_unarchive->c34 = $lotpack->c34;
            $lotpack_unarchive->d = $lotpack->d;
            $lotpack_unarchive->d14 = $lotpack->d14;
            $lotpack_unarchive->d12 = $lotpack->d12;
            $lotpack_unarchive->d34 = $lotpack->d34;
            $lotpack_unarchive->e = $lotpack->e;
            $lotpack_unarchive->e14 = $lotpack->e14;
            $lotpack_unarchive->e12 = $lotpack->e12;
            $lotpack_unarchive->e34 = $lotpack->e34;
            $lotpack_unarchive->f = $lotpack->f;
            $lotpack_unarchive->f14 = $lotpack->f14;
            $lotpack_unarchive->f12 = $lotpack->f12;
            $lotpack_unarchive->f34 = $lotpack->f34;
            $lotpack_unarchive->micro = $lotpack->micro;
            $lotpack_unarchive->fibra = $lotpack->fibra;
            $lotpack_unarchive->color_id = $lotpack->color_id;
            $lotpack_unarchive->notas = $lotpack->notas;
            $lotpack_unarchive->vendedor_id = $lotpack->vendedor_id;
            $lotpack_unarchive->save();

            foreach ($lotpack->numeraciones as $numeracion)
            {
                $numeracion_unarchive = new Numeracion;
                $numeracion_unarchive->lotpack_id = $lotpack_unarchive->id;
                $numeracion_unarchive->sort_order = $numeracion->sort_order;
                $numeracion_unarchive->calidad = $numeracion->calidad;
                $numeracion_unarchive->type = $numeracion->type;
                $numeracion_unarchive->fardos = $numeracion->fardos;
                $numeracion_unarchive->num_start = $numeracion->num_start;
                $numeracion_unarchive->num_end = $numeracion->num_end;
                $numeracion_unarchive->numeracion = $numeracion->numeracion;
                $numeracion_unarchive->save();
            }

        }

    }

    static function packLote($lote, $packed_lots)
    {

        foreach ($packed_lots as $packed_lot )
        {
            // dd($packed_lot);
            // Ya no está disponible
            $pack_original = Pack::where(['id' => $packed_lot['parent_id']])->first();
            $pack_original->status = 2;
            $pack_original->save();

            $lotpack = Lotpack::create([
                'lote_id' => $lote->id,
                'parent_id' => $pack_original->id,
                'fecha' => Carbon::createFromFormat('d/m/Y', $packed_lot['fecha'])->format('Y-m-d'),
                'lote' => $packed_lot['lote'],
                'num_start' => (int)self::getNumStart($packed_lot['numeracion']),
                'num_end' => (int)self::getNumEnd($packed_lot['numeracion']),
                'fardos' => $packed_lot['fardos'],
                'b' => $packed_lot['b'], 'b14' => $packed_lot['b14'], 'b12' => $packed_lot['b12'], 'b34' => $packed_lot['b34'],
                'c' => $packed_lot['c'], 'c14' => $packed_lot['c14'], 'c12' => $packed_lot['c12'], 'c34' => $packed_lot['c34'],
                'd' => $packed_lot['d'], 'd14' => $packed_lot['d14'], 'd12' => $packed_lot['d12'], 'd34' => $packed_lot['d34'],
                'e' => $packed_lot['e'], 'e14' => $packed_lot['e14'], 'e12' => $packed_lot['e12'], 'e34' => $packed_lot['e34'],
                'f' => $packed_lot['f'], 'f14' => $packed_lot['f14'], 'f12' => $packed_lot['f12'], 'f34' => $packed_lot['f34'],
                'micro' => $packed_lot['micro'],
                'fibra' => $packed_lot['fibra'],
                'color_id' => $packed_lot['color_id'],
                'notas' => $packed_lot['notas'],
                'vendedor_id' => $packed_lot['vendedor_id'],
            ]);
            // Guardo numeraciones
            foreach($packed_lot['num_details'] as $num_detail)
            {
                $numeracion = Numeracion::create([
                    'lotpack_id' => $lotpack->id,
                    'sort_order' => $num_detail['sort_order'],
                    'calidad' => $num_detail['calidad'],
                    'type' => $num_detail['type'],
                    'fardos' => $num_detail['fardos'],
                    'num_start' => (int)self::getNumStart($num_detail['numeracion']),
                    'num_end' => (int)self::getNumEnd($num_detail['numeracion']),
                    'numeracion' => $num_detail['numeracion'],
                ]);
            }
        }

        return $packed_lots;

    }

    static function releaseLotpack ($release_lotpack)
    {
        // Vuelvo a disponible el lote original
        $pack_original = Pack::where(['id' => $release_lotpack['parent_id']])->first();
        $pack_original->status = 1;
        $pack_original->save();
        // Elimino el lotpack y su numeracion
        Numeracion::where(['lotpack_id' => $release_lotpack->id])->delete();
        $release_lotpack->delete();
    }

    static function partialLote($lotpack, $lote_id) {

        if ( count($lotpack['num_removed']) > 0) // Hay muestras a remove
        {
            $pack_original = Pack::where(['id' => $lotpack['parent_id']])->first();
            self::removePackSamples($pack_original, $lotpack['num_removed']);
            $pack_original->status = 1; // Lo devuelvo a stock
            $pack_original->save();

            $numeraciones = [];
            for ( $nd = 0; $nd < count($lotpack['num_details']); $nd++ ) {
                $lotpack['num_details'][$nd]['removed'] = false;
                $lotpack[$lotpack['num_details'][$nd]['type']] = (int)$lotpack['num_details'][$nd]['fardos'];
                $numeraciones[] = (object)$lotpack['num_details'][$nd];
            }

            $total_removed = 0;
            for ( $nr = 0; $nr < count($lotpack['num_removed']); $nr++ ) {
                $lotpack['num_removed'][$nr]['removed'] = true;
                $lotpack[$lotpack['num_removed'][$nr]['type']] = null;
                $total_removed += (int)$lotpack['num_removed'][$nr]['fardos'];
                $numeraciones[] = (object)$lotpack['num_removed'][$nr];
            }
            $lotpack['fardos'] -= $total_removed;
            $lotpack['numeraciones'] = $numeraciones;
            $lotpack['lote_id'] = $lote_id;
            $lotpack['fecha'] = Carbon::createFromFormat('d/m/Y', $lotpack['fecha'])->format('Y-m-d');
            self::archive((object)$lotpack, true);

        }

    }

    static function removePackSamples($pack, $num_removed)
    {
        $total_to_remove = 0;
        for ( $n = 0; $n < count($num_removed); $n++ )
        {
            $num_to_remove = $num_removed[$n];
            $pack[$num_to_remove['type']] = null; // 0
            $total_to_remove += (int)$num_to_remove['fardos'];
        }
        $pack['fardos'] -= $total_to_remove;
    }

    static function releaseLotpackHistory ($release_lotpack)
    {
        // Vuelvo a disponible el lote original
        $pack_original = Pack::where(['id' => $release_lotpack['parent_id']])->first();
        $pack_original->status = 2;
        $pack_original->save();
        // Elimino el lotpack y su numeracion
        NumeracionHistory::where(['lotpack_id' => $release_lotpack->id])->delete();
        $release_lotpack->delete();
    }

    static function getLote($id, $type = '')
    {
        $lote = Lote::with('lotpacks', 'lotpacks.numeraciones', 'history', 'history.numeraciones')
            ->where(['id' => $id])
            ->first();

        $data = [
            'id' => $lote->id,
            'vendedor_id' => $lote->vendedor_id,
            'num_start' => $lote->num_start,
            'num_end' => $lote->num_end,
            'numeracion' => $lote->numeracion,
            'code' => $lote->code,
            'fecha' => $lote->fecha_human,
        ];

        $lotpacks = [];
        if ( $type == 'archived' ) {
            $the_lotpacks = $lote->history;
        }
        else
        {
            $the_lotpacks = $lote->lotpacks;
        }
        foreach ( $the_lotpacks as $lotpack )
        {
            $lotpack_data = [
                'id' => $lotpack->parent_id,
                'parent_id' => $lotpack->parent_id,
                'fecha' => $lotpack->fecha_human,
                'lote' => $lotpack->lote,
                'num_start' => $lotpack->num_start,
                'num_end' => $lotpack->num_end,
                'numeracion' => $lotpack->numeracion,
                'fardos' => $lotpack->fardos,
                'b' => $lotpack->b,
                'b14' => $lotpack->b14,
                'b12' => $lotpack->b12,
                'b34' => $lotpack->b34,
                'c' => $lotpack->c,
                'c14' => $lotpack->c14,
                'c12' => $lotpack->c12,
                'c34' => $lotpack->c34,
                'd' => $lotpack->d,
                'd14' => $lotpack->d14,
                'd12' => $lotpack->d12,
                'd34' => $lotpack->d34,
                'e' => $lotpack->e,
                'e14' => $lotpack->e14,
                'e12' => $lotpack->e12,
                'e34' => $lotpack->e34,
                'f' => $lotpack->f,
                'f14' => $lotpack->f14,
                'f12' => $lotpack->f12,
                'f34' => $lotpack->f34,
                'micro' => $lotpack->micro,
                'fibra' => $lotpack->fibra,
                'color_id' => $lotpack->color_id,
                'notas' => $lotpack->notas,
                'vendedor_id' => $lotpack->vendedor_id,
            ];
            $numeraciones = [];
            foreach ( $lotpack->numeraciones as $numeracion )
            {
                $numeracion_data = [
                    'id' => $numeracion->id,
                    'sort_order' => $numeracion->sort_order,
                    'calidad' => $numeracion->calidad,
                    'type' => $numeracion->type,
                    'fardos' => $numeracion->fardos,
                    'num_start' => $numeracion->num_start,
                    'num_end' => $numeracion->num_end,
                    'numeracion' => $numeracion->numeracion,
                    'removed' => $numeracion->removed,
                ];
                $numeraciones[] = $numeracion_data;
            }
            $lotpack_data['num_details'] = $numeraciones;
            $lotpacks[] = $lotpack_data;
        }
        $data['lotpacks'] = $lotpacks;
        return $data;
    }

    static function getNumStart ($numeracion)
    {
        if ( strpos($numeracion, '/'))
        {
            $nums = explode('/', $numeracion);
            return $nums[0];
        }
        else
        {
            return null;
        }
    }

    static function getNumEnd ($numeracion)
    {
        if ( strpos($numeracion, '/'))
        {
            $nums = explode('/', $numeracion);
            return $nums[1];
        }
        else
        {
            return null;
        }
    }

}