<?php

namespace App\Http\Controllers\API;

use App\Color;
use App\Despacho;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Validator;

class DespachosController extends BaseController
{

    protected $rules = [
        'item.remito_id' => 'required',
        // 'item.item_code' => 'required|max:3'
    ];

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $data = Despacho::with('remito', 'remito.lote', 'remito.lote.lotpacks', 'remito.comprador', 'remito.vendedor')->orderBy('created_at', 'desc')->get();
        return $this->success($data);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {

        $request->validate($this->rules);
        $item = $request->input('item');
        unset($item['fecha_human']);

        if ( is_null($item['id']) )
        {
            $saved = Despacho::create($item);
        }
        else
        {
            $saved = Despacho::where([
               'id' => $item['id']
            ])->update($this->filterMeta($item));
        }

        return $this->success($saved);
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        $data = Despacho::where(['id' => $id])->select([
            'id',
            'remito_id',
            'fardos',
            'micro',
            'fibra',
            'kilos',
            'cosecha',
            'precio',
            'puesto',
            'entrega',
            'embarque',
            'calidad',
            'seguro',
            'pago',
            'arbitraje',
            'kilaje_promedio',
            'observaciones',
        ])->first();
        return $this->success($data);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy(Request $request)
    {
        $id = $request->input('id');
        Despacho::find($id)->delete();
        return $this->success(['ok' => true]);
    }
}
