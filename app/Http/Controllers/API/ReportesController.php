<?php

namespace App\Http\Controllers\API;

use App\Color;
use App\Comprador;
use App\Despacho;
use App\Lotpack;
use App\LotpackHistory;
use App\Pack;
use App\Remito;
use Carbon\Carbon;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Validator;

class ReportesController extends BaseController
{

    public function stock ()
    {
        $lotpacks = Pack::with('vendedor', 'color')
            ->orderBy('created_at', 'desc')
            ->get();

        return $this->success($lotpacks);
    }

    public function stockEvolution ()
    {
        $lotpacks = Pack::all();
        $today = Carbon::today();
        $dates = collect();
        for ( $i = 0; $i - 30; $i++)
        {
            $date_sub = $today->subDay();
            $date = $date_sub->format('Y-m-d');
            $date_ts = $date_sub->timestamp;
            $date_display = $date_sub->format('d/m');
            $total = $lotpacks->reduce(function ($carry, $item) use ($date) {
                return $item->fecha == $date ? $carry + (int)$item->fardos : $carry;
            }, 0);
            $dates->push(['date' => $date, 'date_ts' => $date_ts,'date_display' => $date_display, 'value' => $total]);
        }
        return $this->success($dates);
    }

    public function historico ()
    {
        $lotpacks = LotpackHistory::with('vendedor', 'color', 'lote_detail', 'lote_detail.remito')
            ->orderBy('created_at', 'desc')->get();

        return $this->success($lotpacks);
    }

    public function remitos ()
    {
        $remitos = Remito::with('comprador', 'vendedor', 'lote')
            ->orderBy('created_at', 'desc')
            ->get();

        return $this->success($remitos);
    }

    public function despachos ()
    {
        $despachos = Despacho::with('remito', 'remito.comprador', 'remito.vendedor', 'remito.lote')
            ->orderBy('created_at', 'desc')
            ->get();

        return $this->success($despachos);
    }

}
