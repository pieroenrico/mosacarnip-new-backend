<?php

namespace App\Http\Controllers\API;

use App\Color;
use App\Pack;
use Carbon\Carbon;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

class PacksController extends BaseController
{

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {

        $vendedor_id = request()->input('vendedor_id');

        if ( $vendedor_id )
        {
            $packs = Pack::orderBy('updated_at')
                ->where(['vendedor_id' => $vendedor_id])
                ->inbox()
                ->get();
        }
        else
        {

            $packs = Pack::orderBy('updated_at')
                ->inbox()
                ->get();
        }


        $data = [];
        foreach ( $packs as $pack )
        {
            $data[] = $pack->forInput();
        }

        return $this->success($data);
    }

    public function stock()
    {

        $vendedor_id = request()->input('vendedor_id');

        if ( $vendedor_id )
        {
            $packs = Pack::orderBy('updated_at')
                ->where(['vendedor_id' => $vendedor_id])
                ->stock()
                ->get();
        }
        else
        {
            $packs = Pack::orderBy('updated_at')
                ->stock()
                ->get();
        }

        $data = [];
        foreach ($packs as $pack) {
            $data[] = $pack->forInput();
        }

        return $this->success($data);
    }

    public function stockAvailable()
    {

        $vendedor_id = request()->input('vendedor_id');
        $filter_lote = request()->input('filter_lote');

        $packs_builder = Pack::orderBy('updated_at')
            ->where(['vendedor_id' => $vendedor_id])
            ->available();

        // tienen que ser todos los lotes que tienen status 1 o 2
        // y que no son parte de otro lote
        if ( $filter_lote )
        {
            $packs_for_filter_lote = DB::table('lotpacks')
                ->select('parent_id')
                ->where('lote_id', '!=', $filter_lote)
                ->pluck('parent_id');
            $packs_builder->whereNotIn('id', $packs_for_filter_lote->toArray());
        }

        $packs = $packs_builder->get();

        $data = [];
        foreach ( $packs as $pack )
        {
            $data[] = $pack->forInput();
        }


        return $this->success($data);
    }

    public function create(Request $request)
    {
        $vendedor_id = request()->input('vendedor_id');
        $today = Carbon::today()->format('Y-m-d');
        $pack = Pack::create([
            'fecha' => $today,
            'vendedor_id' => $vendedor_id,
        ]);
        return $this->success($pack->forInput());
    }

    public function status(Request $request)
    {
        $pack_data = $request->input('pack');
        $status = $request->input('status');
        $pack = Pack::where(['id' => $pack_data['id']])->first();
        $pack->status = $status;
        $pack->save();

        return $this->success($pack->forInput());
    }

    public function assign(Request $request)
    {
        $pack_data = $request->input('pack');
        $vendedor_id = $request->input('vendedor_id');
        $pack = Pack::where(['id' => $pack_data['id']])->first();
        $pack->vendedor_id = $vendedor_id;
        $pack->save();

        return $this->success($pack->forInput());
    }

    public function destroy(Request $request)
    {
        $pack_data = $request->input('pack');
        $status = $request->input('status');
        $pack = Pack::where(['id' => $pack_data['id']])->first();
        $pack->delete();

        return $this->success($pack->forInput());
    }

    public function update(Request $request)
    {
        $pack_data = $request->input('pack');

        $pack = Pack::where(['id' => $pack_data['id']])->first();
        $pack->fecha = Carbon::createFromFormat('d/m/Y', $pack_data['fecha'])->format('Y-m-d');
        $pack->lote = $pack_data['lote'];
        $pack->num_start = (int)$this->getNumStart($pack_data['numeracion']);
        $pack->num_end = (int)$this->getNumEnd($pack_data['numeracion']);
        $pack->fardos = $pack_data['fardos'];
        $pack->b = $pack_data['b']; $pack->b14 = $pack_data['b14']; $pack->b12 = $pack_data['b12']; $pack->b34 = $pack_data['b34'];
        $pack->c = $pack_data['c']; $pack->c14 = $pack_data['c14']; $pack->c12 = $pack_data['c12']; $pack->c34 = $pack_data['c34'];
        $pack->d = $pack_data['d']; $pack->d14 = $pack_data['d14']; $pack->d12 = $pack_data['d12']; $pack->d34 = $pack_data['d34'];
        $pack->e = $pack_data['e']; $pack->e14 = $pack_data['e14']; $pack->e12 = $pack_data['e12']; $pack->e34 = $pack_data['e34'];
        $pack->f = $pack_data['f']; $pack->f14 = $pack_data['f14']; $pack->f12 = $pack_data['f12']; $pack->f34 = $pack_data['f34'];
        $pack->micro = $pack_data['micro'];
        $pack->fibra = $pack_data['fibra'];
        $pack->notas = $pack_data['notas'];
        $pack->color_id = $pack_data['color_id'];
        $pack->vendedor_id = $pack_data['vendedor_id'];
        $pack->save();

        return $this->success(['ok' => 1]);
    }

    private function getNumStart ($numeracion)
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
    private function getNumEnd ($numeracion)
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

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        $data = Color::where(['id' => $id])->select(['id', 'nombre', 'item_code'])->first();
        return $this->success($data);
    }

}
