<?php

namespace App\Http\Controllers\API;

use App\Color;
use App\Lote;
use App\Lotpack;
use App\Numeracion;
use App\NumeracionHistory;
use App\Pack;
use App\Remito;
use App\Workers\Mosa;
use App\Workers\Packer;
use Carbon\Carbon;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Validator;

class RemitosController extends BaseController
{

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $remitos = Remito::with('vendedor', 'comprador', 'vendedor.provincia', 'vendedor.localidad', 'comprador.provincia', 'comprador.localidad')
            ->orderBy('id', 'desc')
            ->get();

        return $this->success($remitos);
    }

    public function destroy(Request $request)
    {
        /*$pack_data = $request->input('pack');
        $status = $request->input('status');
        $pack = Pack::where(['id' => $pack_data['id']])->first();
        $pack->delete();

        return $this->success($pack->forInput());*/
    }


    public function show($id)
    {

        $remito = Remito::with('lote')
            ->where(['id' => $id])
            ->first();

        $data = [
            'id' => $remito->id,
            'vendedor_id' => $remito->vendedor_id,
            'comprador_id' => $remito->comprador_id,
            'status' => $remito->status,
            'fecha' => $remito->fecha_human,
            'code' => $remito->code,
            'notas' => $remito->notas,
            'lote' => Mosa::get_lote($remito->lote->id, $remito->isArchived ? 'archived' : '')
        ];

        return $this->success($data);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request)
    {
        $remito = Mosa::update_remito($request->input());
        return $this->success($remito);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {

        $remito = Mosa::store_remito($request->input());
        return $this->success($remito);

    }

    public function status (Request $request)
    {
        $remito_id = $request->input('remito_id');
        $status = $request->input('status');

        switch ($status)
        {
            case 0: // confirm
                Mosa::confirm_remito($remito_id);
                break;
            case 1: // approve total
                Mosa::approve_remito($remito_id);
                break;
            case 2: // approve partial
                Mosa::partial_approve_remito($request->input());
                break;
            case 3: // reject
                Mosa::reject_remito($remito_id);
                break;
        }
    }
    private function confirm($remito)
    {

        if ( $remito->status == 3) // si el estado del remito era rechazado, desarchivo los anteriores
        {
            $lotpacks_to_collect = $remito->lotpacks;
            foreach ( $lotpacks_to_collect as $release_lotpack )
            {
                /**
                 * Guardo el lotpack history
                 */
                Packer::unarchive($release_lotpack);
                Packer::releaseLotpackHistory($release_lotpack);
            }
        }
        // en caso de que el estado era 1 (aprobado), entonces no desarchivo, no es necesario

        if ( $remito->status == 2) { // si el estado del remito es rechazado parcial, hago los merges necesarios
            $lotpacks_to_collect = $remito->lotpacks;
            foreach ( $lotpacks_to_collect as $release_lotpack ) {

                $pack_original = Pack::where(['id' => $release_lotpack->parent_id])->first();
                $total = 0;
                foreach ( $release_lotpack->numeraciones as $numeracion )
                {
                    if ($numeracion->removed) {
                        $release_lotpack[$numeracion['type']] += (int)$numeracion['fardos'];
                        $pack_original[$numeracion['type']] += (int)$numeracion['fardos'];
                        $total += (int)$numeracion['fardos'];
                    }
                }
                $release_lotpack->fardos += $total;
                $pack_original->fardos += $total;
                $pack_original->save();
                Packer::unarchive($release_lotpack);
                Packer::releaseLotpackHistory($release_lotpack);
            }
        }

        $remito->status = 0;
        $remito->save();
    }
    private function approve($remito)
    {
        /**
         * Guardo el lotpack history
         */
        $remito->status = 1;
        $remito->save();
    }
    public function partial(Request $request)
    {

        Mosa::partial_approve_remito($request->input());
        dd('done');

        $delivery_id = $request->input('delivery_id');
        $packed_lots = $request->input('packs');
        $numeracion = $request->input('numeracion');
        $notas = $request->input('notas');
        $lote_id = $request->input('lote_id');

        $remito = Remito::where(['id' => $delivery_id])
            ->first();

        foreach ( $packed_lots as $packed_lot )
        {
            Packer::partialLote($packed_lot, $lote_id);
        }

        $lotpacks_for_this_lot = Lotpack::where(['lote_id' => $lote_id]);
        $numeraciones_for_this_lot = Numeracion::whereIn('lotpack_id', $lotpacks_for_this_lot->pluck('id')->toArray())->delete();
        $lotpacks_for_this_lot->delete();

        $remito->status = 2;
        $remito->save();

        $this->success($remito);
    }
    private function reject($remito)
    {

        $lotpacks_to_release = $remito->lotpacks;
        foreach ( $lotpacks_to_release as $release_lotpack )
        {
            /**
             * Guardo el lotpack history
             */
            Packer::archive($release_lotpack);
            Packer::releaseLotpack($release_lotpack);
        }

        $remito->status = 3;
        $remito->save();
    }



}
