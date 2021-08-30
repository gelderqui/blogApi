<?php
namespace App\Helpers;

use Firebase\JWT\JWT;
use Illuminate\Support\Facades\DB;
use App\User;

class JwtAuth{

    public $key;

    public function __construct(){
        $this->key = 'esto_es_una_clave_secreta-121023';
    }

    public function signup($email,$password, $getToken=null){
        //Buscar si existe el usuario con sus credenciales
        $user = User::where([
            'email'=> $email,
            'password'=> $password
        ])->first();//Este metodo solo saca un registro

        //Comprobar si son correctas(objetos)
        $signup = false;
        if(is_object($user)){
            $signup = true;
        }
        //Generar el token con los datos del usuario identificado
        if($signup){//Comprovar si signup es true
            $token = array(
                'sub'       => $user->id,
                'email'     => $user->email,
                'name'      => $user->name,
                'surname'   => $user->surname,
                'iat'       => time(),
                'exp'       => time()+(7*24*60*60),//Fecha en que caducara el token
                'description'=>$user->description
            );

            $jwt = JWT::encode($token, $this->key,'HS256');//Key es una clave unica dentro del backend
            $decoded = JWT::decode($jwt,$this->key, ['HS256']);
        //Devolver los datos decodificados o el token, en funcion de un parametro
            if(is_null($getToken)){
                $data = $jwt;
            }else{
                $data = $decoded;
            }

        }else{
            $data = array(
                'status'=>'error',
                'message'=>'login incorrecto'
            );
        }
        return $data;
    }
    public function checkToken($jwt, $getIdentity=false)
    {
        $auth=false;
        try{
            $jwt = str_replace('"','',$jwt);
            $decoded=JWT::decode($jwt,$this->key,['HS256']);

        }catch(\UnexpectedValueException $e){
            $auth=false;
        }catch(\DomainException $e){
            $auth=false;
        }

        if(!empty($decoded)&& is_object($decoded) && isset($decoded->sub)){
            $auth = true;
        }else{
            $auth=false;
        }

        if($getIdentity){
            return $decoded;
        }

        return $auth;
    }

}
