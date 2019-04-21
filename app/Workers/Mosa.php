<?php

namespace App\Workers;

use App\Lote;
use App\Lotpack;
use App\LotpackHistory;
use App\Numeracion;
use App\NumeracionHistory;
use App\Pack;
use App\Remito;
use Carbon\Carbon;

class Mosa {

    public const PACK_STATUS_INGRESO = 0;
    public const PACK_STATUS_DISPONIBLE = 1;
    public const PACK_STATUS_NO_DISPONIBLE = 2;

    public const REMITO_STATUS_A_CONFIRMAR = 0;
    public const REMITO_STATUS_APROBADO = 1;
    public const REMITO_STATUS_RECHAZADO = 3;
    public const REMITO_STATUS_APROBADO_PARCIAL = 2;

    // Remito => Lote => Lotpacks (packs) => Numeraciones
    //              => HistoryLotpacks (packs) => HistoryNumeraciones

    /*******************************************
     * REMITOS
     *******************************************/
    static function store_remito($data)
    {
        $lotpacks = self::param('packs', $data);
        $built_lot = self::param('built_lot', $data);
        $vendedor_id = self::param('vendedor_id', $data);
        $comprador_id = self::param('comprador_id', $data);
        $numeracion = self::param('numeracion', $data);
        $notas = self::param('notas', $data);

        $remito = Remito::create([
            'fecha' => Carbon::today()->format('Y-m-d'),
            'vendedor_id' => $vendedor_id,
            'comprador_id' => $comprador_id,
            'notas' => $notas,
        ]);

        if ( $built_lot )
        {
            $lote = Lote::where(['id' => $built_lot])->first();
            $lote->remito_id = $remito->id;
            $lote->save();
        }
        else
        {
            // Creo un lote para este remito
            $lote = Mosa::create_lote([
                'vendedor_id' => $vendedor_id,
                'numeracion' => $numeracion,
                'remito_id' => $remito->id,
                'lotpacks' => $lotpacks,
            ]);
        }

        return $remito;
    }

    static function update_remito($data)
    {
        $remito_id = self::param('delivery_id', $data);
        $lotpacks = self::param('packs', $data);
        $numeracion = self::param('numeracion', $data);
        $notas = self::param('notas', $data);

        $remito = Remito::with('lote')->where(['id' => $remito_id])->first();
        $remito->notas = $notas;
        $remito->save();

        $lote = self::update_lote([
            'lote' => $remito->lote,
            'numeracion' => $numeracion,
            'lotpacks' => $lotpacks,
        ]);

        return $remito;
    }

    static function confirm_remito($remito_id)
    {
        $remito = Remito::with('lote')->where(['id' => $remito_id])->first();

        if ( $remito->status == Mosa::REMITO_STATUS_APROBADO ) {
            // Elimino el history
            $lotpacks_to_collect = $remito->lotpacks; // Aca va a devolver los del history
            foreach ( $lotpacks_to_collect as $lotpack_to_collect )
            {
                self::delete_lotpack_history($lotpack_to_collect);
            }
        }

        if ( $remito->status == Mosa::REMITO_STATUS_RECHAZADO )
        {
            $lotpacks_to_collect = $remito->lotpacks; // Aca va a devolver los del history
            foreach ( $lotpacks_to_collect as $lotpack_to_collect )
            {
                self::history_to_stock($lotpack_to_collect); // lo vuelvo a copiar al lote
                self::delete_lotpack_history($lotpack_to_collect); // lo elimino del history
                self::unrelease_pack($lotpack_to_collect->parent_id); // libero el paquete original
            }
        }

        if ( $remito->status == Mosa::REMITO_STATUS_APROBADO_PARCIAL) { // si el estado del remito es rechazado parcial, hago los merges necesarios

            $lotpacks_to_collect = $remito->lotpacks;

            $lotpacks_to_delete = Lotpack::where(['lote_id' => $remito->lote->id])->delete();

            foreach ( $lotpacks_to_collect as $lotpack_to_collect )
            {
                // Traigo el que está relacionado para que haga las sumas
                $history_lotpacks_removed = LotpackHistory::with('numeraciones')
                    ->where([
                        'history_numeracion_id' => $lotpack_to_collect->id,
                    ])->get();
                $history_lotpacks = $history_lotpacks_removed->push($lotpack_to_collect);

                // Saco los totales para reconstruirlo
                $calidades = [
                    'fardos' => $history_lotpacks->reduce( function ( $total, $lotpack) { return $total += (int)$lotpack['fardos']; }, 0),
                    'b' => $history_lotpacks->reduce( function ( $total, $lotpack) { return $total += (int)$lotpack['b']; }, 0),
                    'b14' => $history_lotpacks->reduce( function ( $total, $lotpack) { return $total += (int)$lotpack['b14']; }, 0),
                    'b12' => $history_lotpacks->reduce( function ( $total, $lotpack) { return $total += (int)$lotpack['b12']; }, 0),
                    'b34' => $history_lotpacks->reduce( function ( $total, $lotpack) { return $total += (int)$lotpack['b34']; }, 0),
                    'c' => $history_lotpacks->reduce( function ( $total, $lotpack) { return $total += (int)$lotpack['c']; }, 0),
                    'c14' => $history_lotpacks->reduce( function ( $total, $lotpack) { return $total += (int)$lotpack['c14']; }, 0),
                    'c12' => $history_lotpacks->reduce( function ( $total, $lotpack) { return $total += (int)$lotpack['c12']; }, 0),
                    'c34' => $history_lotpacks->reduce( function ( $total, $lotpack) { return $total += (int)$lotpack['c34']; }, 0),
                    'd' => $history_lotpacks->reduce( function ( $total, $lotpack) { return $total += (int)$lotpack['d']; }, 0),
                    'd14' => $history_lotpacks->reduce( function ( $total, $lotpack) { return $total += (int)$lotpack['d14']; }, 0),
                    'd12' => $history_lotpacks->reduce( function ( $total, $lotpack) { return $total += (int)$lotpack['d12']; }, 0),
                    'd34' => $history_lotpacks->reduce( function ( $total, $lotpack) { return $total += (int)$lotpack['d34']; }, 0),
                    'e' => $history_lotpacks->reduce( function ( $total, $lotpack) { return $total += (int)$lotpack['e']; }, 0),
                    'e14' => $history_lotpacks->reduce( function ( $total, $lotpack) { return $total += (int)$lotpack['e14']; }, 0),
                    'e12' => $history_lotpacks->reduce( function ( $total, $lotpack) { return $total += (int)$lotpack['e12']; }, 0),
                    'e34' => $history_lotpacks->reduce( function ( $total, $lotpack) { return $total += (int)$lotpack['e34']; }, 0),
                    'f' => $history_lotpacks->reduce( function ( $total, $lotpack) { return $total += (int)$lotpack['f']; }, 0),
                    'f14' => $history_lotpacks->reduce( function ( $total, $lotpack) { return $total += (int)$lotpack['f14']; }, 0),
                    'f12' => $history_lotpacks->reduce( function ( $total, $lotpack) { return $total += (int)$lotpack['f12']; }, 0),
                    'f34' => $history_lotpacks->reduce( function ( $total, $lotpack) { return $total += (int)$lotpack['f34']; }, 0),
                ];

                $pack_merged = Pack::create([
                    'fecha' => $lotpack_to_collect->fecha,
                    'lote' => $lotpack_to_collect->lote,
                    'num_start' => $lotpack_to_collect->num_start,
                    'num_end' => $lotpack_to_collect->num_end,
                    'micro' => $lotpack_to_collect->micro,
                    'fibra' => $lotpack_to_collect->fibra,
                    'color_id' => $lotpack_to_collect->color_id,
                    'notas' => $lotpack_to_collect->notas,
                    'status' => Mosa::PACK_STATUS_NO_DISPONIBLE,
                    'vendedor_id' => $lotpack_to_collect->vendedor_id,
                ]);
                self::merge_calidades($pack_merged, $calidades);
                $pack_merged->fardos = $calidades['fardos'];
                $pack_merged->save();

                $data_new_lotpack = [
                    'lote_id' => $remito->lote->id,
                    'parent_id' => $pack_merged->id,
                    'fecha' => $pack_merged->fecha,
                    'lote' => $pack_merged->lote,
                    'num_start' => $pack_merged->num_start,
                    'num_end' => $pack_merged->num_end,
                    'micro' => $pack_merged->micro,
                    'fibra' => $pack_merged->fibra,
                    'color_id' => $pack_merged->color_id,
                    'notas' => $pack_merged->notas,
                    'vendedor_id' => $pack_merged->vendedor_id,
                ];
                self::merge_calidades($pack_merged, $calidades);
                $data_new_lotpack['fardos'] = $calidades['fardos'];
                $new_lotpack = Lotpack::create($data_new_lotpack);
                foreach ( $history_lotpacks as $history_lotpack )
                {
                    $numeraciones = self::bundle_numeraciones($new_lotpack, $history_lotpack->numeraciones);
                }

                // Elimino el pack original
                foreach ( $history_lotpacks as $lotpack_to_delete )
                {
                    $pack_original = Pack::where(['id' => $lotpack_to_delete->parent_id])->first();
                    $pack_original->parent_id = $pack_merged->id;
                    $pack_original->lote_id = $remito->lote->id;
                    $numeraciones = NumeracionHistory::where(['lotpack_id' => $lotpack_to_delete->id])->get();
                    $pack_original->numeraciones = $numeraciones;
                    self::stock_to_history($pack_original); //armo el history

                    self::delete_lotpack_history($lotpack_to_delete); // elimino el viejo del history
                    $pack_original->delete();
                }

            }
        }

        $remito->status = Mosa::REMITO_STATUS_A_CONFIRMAR;
        $remito->save();
    }

    static function partial_approve_remito ($data)
    {

        $remito_id = self::param('delivery_id', $data);
        $lotpacks = self::param('packs', $data);
        $numeracion = self::param('numeracion', $data);
        $notas = self::param('notas', $data);
        $lote_id = self::param('lote_id', $data);

        $remito = Remito::with('lote')->where(['id' => $remito_id])->first();

        $lotpacks_to_delete = Lotpack::where(['lote_id' => $remito->lote->id])->delete();

        foreach ( $lotpacks as $lotpack )
        {
            $lotpack = (object)$lotpack;
            $pack_original = Pack::where(['id' => $lotpack->parent_id])->first();

            /**
             * Primero creo un nuevo pack con la numeracion y calidades aprobadas
             */
            $new_pack_approved = Pack::create([
                'fecha' => $pack_original->fecha,
                'lote' => $pack_original->lote,
                'num_start' => $pack_original->num_start,
                'num_end' => $pack_original->num_end,
                'micro' => $pack_original->micro,
                'fibra' => $pack_original->fibra,
                'color_id' => $pack_original->color_id,
                'notas' => $pack_original->notas,
                'status' => Mosa::PACK_STATUS_NO_DISPONIBLE,
                'vendedor_id' => $pack_original->vendedor_id,
            ]);
            foreach ( $lotpack->num_details as $num )
            {
                $new_pack_approved[$num['type']] = (int)$num['fardos'];
            }
            $new_pack_approved->fardos = self::get_total_fardos($new_pack_approved);
            $new_pack_approved->save();

            $new_pack_approved->lote_id = $lote_id;
            $new_pack_approved->parent_id = $new_pack_approved->id;
            $new_pack_approved->numeraciones = $lotpack->num_details;
            $new_pack_history = self::stock_to_history($new_pack_approved);

            /**
             * También rearmo el lote del remito
             */
            // Primero elimino los lotpacks actuales

            $data_new_lotpack = [
                'lote_id' => $remito->lote->id,
                'parent_id' => $new_pack_approved->id,
                'fecha' => $new_pack_approved->fecha,
                'lote' => $new_pack_approved->lote,
                'num_start' => $new_pack_approved->num_start,
                'num_end' => $new_pack_approved->num_end,
                'micro' => $new_pack_approved->micro,
                'fibra' => $new_pack_approved->fibra,
                'color_id' => $new_pack_approved->color_id,
                'notas' => $new_pack_approved->notas,
                'vendedor_id' => $new_pack_approved->vendedor_id,
            ];
            self::merge_calidades($data_new_lotpack, $new_pack_approved);
            $data_new_lotpack['fardos'] = self::get_total_fardos($data_new_lotpack);
            $new_lotpack = Lotpack::create($data_new_lotpack);
            $numeraciones = self::bundle_numeraciones($new_lotpack, $lotpack->num_details);

            /**
             * Segundo creo un nuevo pack con la numeracion y calidades rechazados
             */
            $new_pack_rejected = Pack::create([
                'fecha' => $pack_original->fecha,
                'lote' => $pack_original->lote,
                'num_start' => $pack_original->num_start,
                'num_end' => $pack_original->num_end,
                'micro' => $pack_original->micro,
                'fibra' => $pack_original->fibra,
                'color_id' => $pack_original->color_id,
                'notas' => $pack_original->notas,
                'status' => Mosa::PACK_STATUS_DISPONIBLE,
                'vendedor_id' => $pack_original->vendedor_id,
            ]);
            foreach ( $lotpack->num_removed as $num )
            {
                $new_pack_rejected[$num['type']] = (int)$num['fardos'];
            }
            $new_pack_rejected->fardos = self::get_total_fardos($new_pack_rejected);
            $new_pack_rejected->save();

            $new_pack_rejected->lote_id = $lote_id;
            $new_pack_rejected->parent_id = $new_pack_rejected->id;
            $new_pack_rejected->numeraciones = $lotpack->num_removed;
            self::stock_to_history($new_pack_rejected, true, $new_pack_history->id);

            $pack_original->delete();
        }

        $remito->status = Mosa::REMITO_STATUS_APROBADO_PARCIAL;
        $remito->save();
        return $remito;
    }

    static function approve_remito($remito_id)
    {
        $remito = Remito::where(['id' => $remito_id])->first();
        $lotpacks_to_release = $remito->lotpacks;
        foreach ( $lotpacks_to_release as $release_lotpack )
        {
            /**
             * Guardo el lotpack history
             */
            self::stock_to_history($release_lotpack);
        }
        $remito->status = Mosa::REMITO_STATUS_APROBADO;
        $remito->save();
    }

    static function reject_remito($remito_id)
    {
        $remito = Remito::where(['id' => $remito_id])->first();
        $lotpacks_to_release = $remito->lotpacks;
        foreach ( $lotpacks_to_release as $release_lotpack )
        {
            /**
             * Guardo el lotpack history
             */
            self::stock_to_history($release_lotpack);
            self::release_pack($release_lotpack->parent_id);
            self::delete_lotpack($release_lotpack);
        }
        $remito->status = Mosa::REMITO_STATUS_RECHAZADO;
        $remito->save();
    }

    /*******************************************
     * LOTES
     *******************************************/

    static function create_lote($data)
    {

        $vendedor_id = self::param('vendedor_id', $data);
        $numeracion = self::param('numeracion', $data);
        $remito_id = self::param('remito_id', $data);
        $lotpacks = self::param('lotpacks', $data);

        $lote = Lote::create([
            'fecha' => Carbon::today()->format('Y-m-d'),
            'vendedor_id' => $vendedor_id,
            'num_start' => Packer::getNumStart($numeracion),
            'num_end' => Packer::getNumEnd($numeracion),
            'remito_id' => $remito_id,
        ]);
        self::bundle_lote($lote, $lotpacks);
        return $lote;
    }

    static function update_lote($data)
    {
        $lote = self::param('lote', $data);
        $lotpacks = self::param('lotpacks', $data);
        $numeracion = self::param('numeracion', $data);

        // Update de numeración por si cambió
        $lote->num_start = self::getNumStart($numeracion);
        $lote->num_end = self::getNumEnd($numeracion);
        $lote->save();

        // Vuelvo todos los stocks a disponible y luego vuelvo a armar el bundle
        foreach ( $lote->lotpacks as $release_lotpack )
        {
            self::release_pack($release_lotpack->parent_id);
            self::delete_lotpack($release_lotpack);
        }
        self::bundle_lote($lote, $lotpacks);

        return $lote;
    }

    static function get_lote($id, $type = '')
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


    /*******************************************
     * PACKS Y LOTPACKS
     *******************************************/

    static function release_pack ($pack_id)
    {
        // Vuelvo a disponible el lote original
        $pack_original = Pack::where(['id' => $pack_id])->first();
        $pack_original->status = Mosa::PACK_STATUS_DISPONIBLE;
        $pack_original->save();
    }

    static function unrelease_pack ($pack_id)
    {
        // Vuelvo a disponible el lote original
        $pack_original = Pack::where(['id' => $pack_id])->first();
        $pack_original->status = Mosa::PACK_STATUS_NO_DISPONIBLE;
        $pack_original->save();
    }

    static function delete_lotpack ($lotpack)
    {
        // Elimino el lotpack y su numeracion
        Numeracion::where(['lotpack_id' => $lotpack->id])->delete();
        $lotpack->delete();
    }

    static function delete_lotpack_history ($lotpack)
    {
        // Elimino el lotpack y su numeracion
        NumeracionHistory::where(['lotpack_id' => $lotpack->id])->delete();
        $lotpack->delete();
    }

    static function bundle_lote($lote, $lotpacks)
    {
        foreach ( $lotpacks as $lotpack_to_bundle )
        {
            // El pack original pasa a no disponible
            $pack_original = Pack::where(['id' => $lotpack_to_bundle['parent_id']])->first();
            $pack_original->status = Mosa::PACK_STATUS_NO_DISPONIBLE;
            $pack_original->save();

            $data_new_lotpack = [
                'lote_id' => $lote->id,
                'parent_id' => $pack_original->id,
                'fecha' => self::fecha_human_to_fecha_database($lotpack_to_bundle['fecha']),
                'lote' => $lotpack_to_bundle['lote'],
                'num_start' => (int)self::getNumStart($lotpack_to_bundle['numeracion']),
                'num_end' => (int)self::getNumEnd($lotpack_to_bundle['numeracion']),
                'micro' => $lotpack_to_bundle['micro'],
                'fibra' => $lotpack_to_bundle['fibra'],
                'color_id' => $lotpack_to_bundle['color_id'],
                'notas' => $lotpack_to_bundle['notas'],
                'vendedor_id' => $lotpack_to_bundle['vendedor_id'],
            ];
            self::merge_calidades($data_new_lotpack, $lotpack_to_bundle);
            $data_new_lotpack['fardos'] = self::get_total_fardos($data_new_lotpack);
            $new_lotpack = Lotpack::create($data_new_lotpack);
            $numeraciones = self::bundle_numeraciones($new_lotpack, $lotpack_to_bundle['num_details']);
        }

        return $lote;
    }

    static function bundle_numeraciones ( $lotpack, $num_details )
    {
        // Guardo numeraciones
        foreach($num_details as $num_detail)
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
        return $num_details;
    }

    static function history_to_stock ($lotpack)
    {
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

    static function stock_to_history ($lotpack, $is_partial = 0, $belongs_to = null)
    {
        $lotpack_history = new LotpackHistory;
        $lotpack_history->parent_id = $lotpack->parent_id;
        $lotpack_history->history_numeracion_id = $belongs_to;
        $lotpack_history->lote_id = $lotpack->lote_id;
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
            $numeracion = (object)$numeracion;
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

        return $lotpack_history;
    }

    /***
     * helper functions
     */
    static function merge_calidades(&$target, $source)
    {
        $calidades_types = [
            'b', 'b14', 'b12', 'b34',
            'c', 'c14', 'c12', 'c34',
            'd', 'd14', 'd12', 'd34',
            'e', 'e14', 'e12', 'e34',
            'f', 'f14', 'f12', 'f34',
        ];
        foreach( $calidades_types as $calidad_type )
        {
            $target[$calidad_type] = (int)$source[$calidad_type] == 0 ? null : (int)$source[$calidad_type];
        }
    }

    static function get_total_fardos($source)
    {
        $calidades_types = [
            'b', 'b14', 'b12', 'b34',
            'c', 'c14', 'c12', 'c34',
            'd', 'd14', 'd12', 'd34',
            'e', 'e14', 'e12', 'e34',
            'f', 'f14', 'f12', 'f34',
        ];
        $total = 0;
        foreach( $calidades_types as $calidad_type )
        {
            $total += $source[$calidad_type] ? (int)$source[$calidad_type] : 0;
        }
        return $total;
    }

    static function fecha_human_to_fecha_database($fecha)
    {
        return Carbon::createFromFormat('d/m/Y', $fecha)->format('Y-m-d');
    }

    static function fecha_database_to_fecha_human($fecha)
    {
        return Carbon::createFromFormat('Y-m-d', $fecha)->format('d/m/Y');
    }

    static function param($key, $array, $default = null)
    {
        return array_key_exists($key, $array) ? $array[$key] : $default;
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