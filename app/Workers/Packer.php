<?
namespace App\Workers;

use App\Lote;
use App\Lotpack;
use App\Numeracion;
use App\Pack;
use Carbon\Carbon;

class Packer {

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

    static function getLote($id)
    {
        $lote = Lote::with('lotpacks', 'lotpacks.numeraciones')
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
        foreach ( $lote->lotpacks as $lotpack )
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