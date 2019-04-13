<?php namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;

class BaseController extends Controller
{

    protected function success($body, $code = 200)
    {
        return response()->json([
            'body' => $body,
        ], $code);
    }

    protected function error($body, $code)
    {
        return response()->json([
            'error' => $body,
        ], $code);
    }

    protected function filterMeta($array)
    {
        unset($array['id']);
        unset($array['code']);
        return $array;
    }
}