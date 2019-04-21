<?php

namespace App\Http\Controllers\API;

use App\Color;
use App\Lote;
use App\Lotpack;
use App\Numeracion;
use App\Pack;
use App\Workers\Mosa;
use App\Workers\Packer;
use Carbon\Carbon;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Validator;

class LotesController extends BaseController
{

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {

        $lotes = Lote::with('lotpacks', 'vendedor')
            ->orderBy('id', 'desc')
            ->pure()
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

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {

        $lotpacks = $request->input('packs');
        $vendedor_id = $request->input('vendedor_id');
        $numeracion = $request->input('numeracion');

        // Creo un lote para este remito
        $lote = Mosa::create_lote([
            'vendedor_id' => $vendedor_id,
            'numeracion' => $numeracion,
            'lotpacks' => $lotpacks,
        ]);

        return $this->success($lote);
    }


    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request)
    {

        $lot_id = $request->input('lot_id');
        $lotpacks = $request->input('packs');
        $numeracion = $request->input('numeracion');

        $lote = Lote::with('lotpacks')->where(['id' => $lot_id])->first();
        $lote = Mosa::update_lote([
            'lote' => $lote,
            'numeracion' => $numeracion,
            'lotpacks' => $lotpacks,
        ]);
        return $this->success($lote);
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        $data = Packer::getLote($id);
        return $this->success($data);
    }

}
