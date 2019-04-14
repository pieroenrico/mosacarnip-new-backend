<?php

namespace App\Http\Controllers\API;

use App\Color;
use App\Lote;
use App\Lotpack;
use App\Numeracion;
use App\Pack;
use App\Remito;
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
        $lotes = Remito::with('vendedor', 'comprador')
            ->orderBy('id', 'desc')
            ->get();

        return $this->success($lotes);
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
            'lote' => Packer::getLote($remito->lote->id, $remito->isArchived ? 'archived' : '')
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

        $delivery_id = $request->input('delivery_id');
        $packed_lots = $request->input('packs');
        $numeracion = $request->input('numeracion');
        $notas = $request->input('notas');

        $remito = Remito::with('lote')->where(['id' => $delivery_id])->first();
        $remito->notas = $notas;
        $remito->save();

        $remito->lote->num_start = Packer::getNumStart($numeracion);
        $remito->lote->num_end = Packer::getNumEnd($numeracion);
        $remito->lote->save();

        // releasing current lots
        foreach ( $remito->lote->lotpacks as $release_lotpack )
        {
            Packer::releaseLotpack($release_lotpack);
        }
        $packed_lots = Packer::packLote($remito->lote, $packed_lots);
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

        $packed_lots = $request->input('packs');
        $built_lot = $request->input('built_lot');
        $vendedor_id = $request->input('vendedor_id');
        $comprador_id = $request->input('comprador_id');
        $numeracion = $request->input('numeracion');
        $notas = $request->input('notas');

        $remito = Remito::create([
            'fecha' => Carbon::today()->format('Y-m-d'),
            'vendedor_id' => $vendedor_id,
            'comprador_id' => $comprador_id,
            'notas' => $notas,
        ]);

        if ( $built_lot ) // Tiene un lote ya armado
        {
            $lote = Lote::where(['id' => $built_lot])->first();
            $lote->remito_id = $remito->id;
            $lote->save();
        }
        else
        {
            $lote = Lote::create([
                'fecha' => Carbon::today()->format('Y-m-d'),
                'vendedor_id' => $vendedor_id,
                'num_start' => Packer::getNumStart($numeracion),
                'num_end' => Packer::getNumEnd($numeracion),
                'remito_id' => $remito->id,
            ]);
            $lotpacks = Packer::packLote($lote, $packed_lots);
        }

        return $this->success($remito);

    }

    public function status (Request $request)
    {
        $remito_id = $request->input('remito_id');
        $status = $request->input('status');

        $remito = Remito::where(['id' => $remito_id])->first();

        switch ($status)
        {
            case 0: // confirm
                $this->confirm($remito);
                break;
            case 1: // approve total
                $this->approve($remito);
                break;
            case 2: // approve partial
                $this->partial($remito);
                break;
            case 3: // reject
                $this->reject($remito);
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
    private function partial($remito)
    {
        $remito->status = 2;
        $remito->save();
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
