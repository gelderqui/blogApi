<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Http\Response;
use App\Category;

class CategoryController extends Controller
{
  //se comento para que no pida autenticacion
  //Ver estos permisos para que el bot funcione correctamente
    public function __construct(){
        $this->middleware('api.auth',['except'=>['index','show','store']]);
    }

    public function index(){
        $categories = Category::all();
        return response()->json([
                'code'=>200,
                'status'=>'success',
                'categories'=>$categories
        ]);
    }

    public function show($id){
        $category = Category::find($id);
        if(is_object($category)){
            $data = [
                'code'=>200,
                'status'=>'successss',
                'category'=>$category,
                'chat'=>$category
            ];
        }else{
            $data = [
                'code'=>404,
                'status'=>'error',
                'message'=>'La categoria no existezz'
            ];
        }
        return response()->json($data, $data['code']);
    }
    public function store(Request $request){
        //Recoger los datos por post
        //Recibir parametros con x-www
        //$json = $request->input('chat',null);
        //$params_array = json_decode($json);

        //dd es para parar y mostrar
        //dd($json);
        //Recibir directamente un json
        $params_array = $request->input('chat',null);

        if(!empty($params_array)){


            //Validar datos
            $validate = \Validator::make($params_array, [
                'nname'=>'required'
            ]);

            //Guardar categoria
            if($validate->fails()){
                $data = [
                    'code'=>404,
                    'status'=>'error',
                    'message'=>'No se ha guardado la categoria'
                ];
            }else{
                $category = new Category();
                $category->name = $params_array['nname'];
                $category->save();
                $data = [
                    'code'=>200,
                    'status'=>'success',
                    'chat'=> $category
                ];
            }
        }else{
            $data = [
                'code'=>404,
                'status'=>'error',
                'message'=>'No se ha enviado ninguna categoria'
            ];

        }
        //Devolver Resultado
        return response()->json($data, $data['code']);

    }
    public function update($id,Request $request){
        //Recoger los datos por post
        $json = $request->input('json',null);
        $params_array = json_decode($json,true);

        if(!empty($params_array)){
            //Validar datos
            $validate = \Validator::make($params_array, [
                'name'=>'required'
            ]);
            //Quitar lo que no quiero actualizar
            unset($params_array['id']);
            unset($params_array['created_at']);

            //Actualizar categoria
            $category = Category::where('id',$id)->update($params_array);
            $data = [
                'code'=>200,
                'status'=>'success',
                'category'=> $params_array
            ];
        }else{
            $data = [
                'code'=>404,
                'status'=>'error',
                'message'=>'No se ha enviado ninguna categoria'
            ];

        }
        //Devolver Resultado
        return response()->json($data, $data['code']);

    }
}
