<?php

use Illuminate\Http\Request;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/

Route::post('oauth/token', 'API\\AccessTokenController@issueToken');
Route::post('login', 'API\\OAuthController@login');

Route::get('colores', 'API\\ColorsController@index')->name('colores.index');
Route::post('colores', 'API\\ColorsController@store')->name('colores.store');
Route::post('colores/destroy', 'API\\ColorsController@destroy')->name('colores.destroy');
Route::get('colores/{id}', 'API\\ColorsController@show')->name('colores.show');

Route::get('provincias', 'API\\ProvinciasController@index')->name('provincias.index');
Route::post('provincias', 'API\\ProvinciasController@store')->name('provincias.store');
Route::post('provincias/destroy', 'API\\ProvinciasController@destroy')->name('provincias.destroy');
Route::get('provincias/{id}', 'API\\ProvinciasController@show')->name('provincias.show');

Route::get('localidades', 'API\\LocalidadesController@index')->name('localidades.index');
Route::post('localidades', 'API\\LocalidadesController@store')->name('localidades.store');
Route::post('localidades/destroy', 'API\\LocalidadesController@destroy')->name('localidades.destroy');
Route::get('localidades/{id}', 'API\\LocalidadesController@show')->name('localidades.show');

Route::get('compradores', 'API\\CompradoresController@index')->name('compradores.index');
Route::post('compradores', 'API\\CompradoresController@store')->name('compradores.store');
Route::post('compradores/destroy', 'API\\CompradoresController@destroy')->name('compradores.destroy');
Route::get('compradores/{id}', 'API\\CompradoresController@show')->name('compradores.show');

Route::get('vendedores', 'API\\VendedoresController@index')->name('vendedores.index');
Route::post('vendedores', 'API\\VendedoresController@store')->name('vendedores.store');
Route::post('vendedores/destroy', 'API\\VendedoresController@destroy')->name('vendedores.destroy');
Route::get('vendedores/{id}', 'API\\VendedoresController@show')->name('vendedores.show');

Route::get('despachos', 'API\\DespachosController@index')->name('despachos.index');
Route::post('despachos', 'API\\DespachosController@store')->name('despachos.store');
Route::post('despachos/destroy', 'API\\DespachosController@destroy')->name('despachos.destroy');
Route::get('despachos/{id}', 'API\\DespachosController@show')->name('despachos.show');

Route::get('packs', 'API\\PacksController@index')->name('packs.index');
Route::get('stock', 'API\\PacksController@stock')->name('packs.stock');
Route::get('stock/available', 'API\\PacksController@stockAvailable')->name('packs.stock.available');
Route::post('packs/create', 'API\\PacksController@create')->name('packs.create');
Route::post('packs/update', 'API\\PacksController@update')->name('packs.update');
Route::post('packs/status', 'API\\PacksController@status')->name('packs.status');
Route::post('packs/destroy', 'API\\PacksController@destroy')->name('packs.destroy');
Route::post('packs/assign', 'API\\PacksController@assign')->name('packs.assign');

Route::get('lotes', 'API\\LotesController@index')->name('lotes.index');
Route::get('lotes/{lote_id}', 'API\\LotesController@show')->name('lotes.show');
Route::post('lotes', 'API\\LotesController@store')->name('lotes.store');
Route::post('lotes/update', 'API\\LotesController@update')->name('lotes.update');

Route::get('remitos', 'API\\RemitosController@index')->name('remitos.index');
Route::get('remitos/{remito_id}', 'API\\RemitosController@show')->name('remitos.show');
Route::post('remitos', 'API\\RemitosController@store')->name('remitos.store');
Route::post('remitos/update', 'API\\RemitosController@update')->name('remitos.update');
Route::post('remitos/partial', 'API\\RemitosController@partial')->name('remitos.partial');
Route::post('remitos/status', 'API\\RemitosController@status')->name('remitos.status');

Route::get('reportes/historico', 'API\\ReportesController@historico')->name('reportes.historico');
Route::get('reportes/stock', 'API\\ReportesController@stock')->name('reportes.stock');
Route::get('reportes/remitos', 'API\\ReportesController@remitos')->name('reportes.remitos');
Route::get('reportes/despachos', 'API\\ReportesController@despachos')->name('reportes.despachos');
Route::get('reportes/stock/evolution', 'API\\ReportesController@stockEvolution')->name('reportes.stock-evolution');


Route::middleware('auth:api')->get('/user', function (Request $request) {
    return $request->user();

});
