<?php

namespace App\Http\Controllers\API;

use App\Color;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Validator;

class ColorsController extends BaseController
{

    protected $rules = [
        'item.nombre' => 'required|min:5|max:20',
        'item.item_code' => 'required|max:3'
    ];

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $data = Color::orderBy('nombre')->get();
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
            $saved = Color::create($item);
        }
        else
        {
            $saved = Color::where([
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
        $data = Color::where(['id' => $id])->select(['id', 'nombre', 'item_code'])->first();
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
        Color::find($id)->delete();
        return $this->success(['ok' => true]);
    }
}
