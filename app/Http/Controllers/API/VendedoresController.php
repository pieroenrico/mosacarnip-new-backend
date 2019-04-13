<?php

namespace App\Http\Controllers\API;

use App\Color;
use App\Comprador;
use App\Vendedor;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Validator;

class VendedoresController extends BaseController
{

    protected $rules = [
        'item.nombre' => 'required|min:5|max:200',
        'item.domicilio' => 'required|min:5|max:200',
        'item.localidad_id' => 'required',
        'item.provincia_id' => 'required',
    ];

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $data = Vendedor::with('provincia', 'localidad')
            ->orderBy('nombre')
            ->get();
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

        if ( is_null($item['id']) )
        {
            $saved = Vendedor::create($item);
        }
        else
        {
            $saved = Vendedor::where([
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
        $data = Vendedor::where(['id' => $id])->select([
            'id',
            'nombre',
            'domicilio',
            'localidad_id',
            'provincia_id',
            'codigo_postal',
            'iva',
            'cuit',
            'email',
            'telefono',
            'fax',
            'contacto',
            'horario',
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
        Vendedor::find($id)->delete();
        return $this->success(['ok' => true]);
    }
}
