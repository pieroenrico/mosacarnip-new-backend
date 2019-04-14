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
        $lotes = Remito::with('vendedor', 'comprador', 'lotpacks')
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
            'lote' => Packer::getLote($remito->lote->id)
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

        $remito = Remito::with('lote')->where(['id' => $delivery_id])->first();
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
        $vendedor_id = $request->input('vendedor_id');
        $comprador_id = $request->input('comprador_id');
        $numeracion = $request->input('numeracion');

        $remito = Remito::create([
            'fecha' => Carbon::today()->format('Y-m-d'),
            'vendedor_id' => $vendedor_id,
            'comprador_id' => $comprador_id,
        ]);

        $lote = Lote::create([
            'fecha' => Carbon::today()->format('Y-m-d'),
            'vendedor_id' => $vendedor_id,
            'num_start' => Packer::getNumStart($numeracion),
            'num_end' => Packer::getNumEnd($numeracion),
            'remito_id' => $remito->id,
        ]);

        $lotpacks = Packer::packLote($lote, $packed_lots);
        return $this->success($remito);

    }

}
