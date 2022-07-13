<?php

use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/

//Cargando clases
use App\Http\Middleware\ApiAuthMiddleware;

Route::get('/', function () {
    return view('welcome');
});

//! rutas de usuario/
//Ruta de prueba
Route::get('/api/pruebas','UserController@pruebas');

//Sin seguridad (no requiere del token)
Route::post('/api/register','UserController@register');
Route::post('/api/login','UserController@login');

//Llamo a mi service provider desde mi controlador para validar mi token
Route::put('/api/user/update','UserController@update');

//Se crea un Middleware(Metodo que se ejecuta antes que un controlador) para no estar repitiendo el codigo de user/update
Route::post('/api/user/upload','UserController@upload')->middleware(ApiAuthMiddleware::class);

//Sin seguridad (no requiere del token)
Route::get('/api/user/avatar/{filename}','UserController@getImage');
Route::get('/api/user/detail/{id}','UserController@detail');
//! fin rutas de usuario/

//! rutas de category/
//Rutas protegidas en el controlador donde cargo el contructor del middleware
//Las rutas resource crean automaticamente rutas predefinidas(php artisan route:list)
Route::resource('api/category','CategoryController');
//Ruta extra show
Route::get('api/category/show1/{id}','CategoryController@show1');
Route::post('api/category/store2','CategoryController@store2');
Route::put('api/category/update2/{id}','CategoryController@update2');
//! fin rutas de category/

Route::resource('api/post','PostController');

//Rutas extras
Route::post('api/post/upload','PostController@upload');
Route::get('/api/post/image/{filename}','PostController@getImage');
Route::get('/api/post/category/{id}','PostController@getPostsByCategory');
Route::get('/api/post/user/{id}','PostController@getPostsByUser');
