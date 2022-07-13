<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Http\Response;
Use App\User;

class UserController extends Controller
{

    public function pruebas(Request $request){
        return "accion de pruebas de user controller";
    }

    public function register(Request $request){
        //Recoger datos de mi variable json si no viene el parametro que sea null la variable
        $json = $request->input('json',null);

        //Puedo usar cualquiera de los dos para acceder a los datos recibidos
        $params = json_decode($json);               //objeto para php ej($params->name)
        $params_array = json_decode($json, true);   //array

        if(!empty($params) && !empty($params_array)){

            //Limpiar datos para evitar que de error la validacion
            $params_array = array_map('trim', $params_array);

            //Validar datos
            $validate = \Validator::make($params_array,[
                'name'=> 'required|alpha',
                'surname'=> 'required|alpha',
                'email'=> 'required|email|unique:users',//Comprobar si el usuario ya existe(duplicado)
                'password'=> 'required',
            ]);

            if($validate->fails()){
                //La validacion ha fallado
                $data=array(
                    'status' => 'error',
                    'code' => 404,
                    'message' => 'El usuario no se ha creado',
                    'erros'=> $validate->errors()
                );
            }
            else{
                //Validacion pasada correctamente
                //Cifrar contraseña
                //$pwd = password_hash($params->password, PASSWORD_BCRYPT, ['cost'=>11]); //cost son las veces que se cifra
                $pwd = hash('sha256',$params->password);
                //Crear el usuario
                $user = new User();
                $user->name = $params_array['name'];
                $user->surname = $params_array['surname'];
                $user->email = $params_array['email'];
                $user->password = $pwd;
                $user->role = 'ROLE_USER';
                $user->save();

                $data=array(
                    'status' => 'success',
                    'code' => 200,
                    'message' => 'El usuario se creo correctamente'
                );
            }
        }
        else{
            $data=array(
                'status' => 'error',
                'code' => 404,
                'message' => 'Los datos enviados no son correctos'
            );
        }

        //Convierte un array en json
        return response()->json($data, $data['code']);
    }

    public function login(Request $request){
        $jwtAuth = new \JwtAuth();
       /* $email = 'gelder@gmail.com';
        $password = 'gelder';
        $pwd = hash('sha256',$password);

        return response()->json($jwtAuth->signup($email,$pwd,true));*/

        //Recibir datos por post
        $json = $request->input('json',null);
        $params = json_decode($json);
        $params_array = json_decode($json, true);

        //Validar esos datos
        $validate = \Validator::make($params_array,[
            'email'=> 'required|email',
            'password'=> 'required'
        ]);

        if($validate->fails()){
            //La validacion ha fallado
            $signup = array(
                'status' => 'error',
                'code' => 404,
                'message' => 'El usuario no se a podido logear',
                'errors'=> $validate->errors()
            );
        }else{
            //Cifrar password
             //$pwd = password_hash($password, PASSWORD_BCRYPT, ['cost'=>11]); //No se usa esto porque crea diferentes hash a la hora de encriptar
            $pwd = hash('sha256',$params->password);
            //Devolver tokens o datos
            $signup = $jwtAuth->signup($params->email, $pwd);
            if(!empty($params->gettoken)){
                $signup = $jwtAuth->signup($params->email,$pwd,true);
            }

        }
        return response()->json($signup,200);
    }

    public function update(Request $request){
        //Comprobar si esta identificado
        //Recibo el header
        $token = $request->header('Authorization');
        $jwtAuth = new \JwtAuth();
        $checkToken = $jwtAuth->checkToken($token);
        $json = $request->input('json',null);
        $params_array = json_decode($json,true);

        if($checkToken && !empty($params_array)){
            //Actualizar usuario
            //Recoger datos del post
            //Sacar uduario identificado
            $user = $jwtAuth->checkToken($token,true);
            //Validar datos
            $validate=\Validator::make($params_array,[
                'name'=> 'required|alpha',
                'surname'=> 'required|alpha',
                'email'=> 'required|email|unique:users'.$user->sub//Comprobar si el usuario ya existe(duplicado)
            ]);

            //Quitar campos que no se van a actualizar
                unset($params_array['id']);
                unset($params_array['role']);
                unset($params_array['password']);
                unset($params_array['created_at']);
                unset($params_array['remember_token']);
            //Actualizar usuario
                $user_update = User::where('id',$user->sub)->update($params_array);
            //Devolver array resultado
                $data=array(
                    'status' => 'success',
                    'code' => 200,
                    'user' => $user,
                    'changes'=>$params_array
                );

        }else{
            $data=array(
                'status' => 'error',
                'code' => 400,
                'message' => 'El usuario no esta identificado-',
            );
        }
        return response()->json($data,$data['code']);
    }

    public function upload(Request $request){
        //Recoger los datos de la peticion
        $image = $request->file('file0');

        //Validacion de imagen
        $validate=\Validator::make($request->all(),[
            'file0'=>'required|image|mimes:jpg,jpeg,png,gif'
        ]);

        //guardar imagen
        if(!$image || $validate->fails()){
            $data=array(
                'status' => 'error',
                'code' => 400,
                'message' => 'Error al subir imagen',
            );
        }
        else{
            $image_name=time().$image->getClientOriginalName();
            \Storage::disk('users')->put($image_name,\File::get($image));

            $data=array(
                'code' => 200,
                'status' => 'succsess',
                'image' => $image_name
            );
        }

        return response()->json($data, $data['code']);//->header('Content-Type','text/plain');
    }

    public function getImage($filename){
        //Probar si existe la imagen
        $isset = \Storage::disk('users')->exists($filename);
        if($isset){
            $file = \Storage::disk('users')->get($filename);
            return new Response($file,200);
        }else{
            $data=array(
                'code' => 404,
                'status' => 'error',
                'message' => 'La imagen no existe'
            );
            return response()->json($data, $data['code']);
        }
    }

    public function detail($id){
        $user= User::find($id);

        if(is_object($user)){
            $data = array(
                'code'=>200,
                'status'=>'success',
                'user'=>$user
            );
        }else{
            $data = array(
                'code'=>404,
                'status'=>'error',
                'message'=>'El usuario no existe'
            );
        }
        return response()->json($data, $data['code']);
    }
}
