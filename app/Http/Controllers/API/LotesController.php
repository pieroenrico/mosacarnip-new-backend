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
        $data = $this->getLote($id);
        return $this->success($data);
    }

	public function getLote($id, $type = '')
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

}
