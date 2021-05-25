<?php
namespace App\Helpers;
use App\Models\Election;
use Illuminate\Support\Carbon;

trait MyResponse {

    public static function returnSuccessResponse($message,$code=201){
        $data = ['message' => $message,'code'=>$code];
        return Response()->json($data,$code);
    }
    public static function returnDataResponse($data,$code=200){
        return response()->json(["data"=>$data,"code"=>$code], $code);
    }
    public static function returnErrorResponse($message="An error has occurred",$code=400){
        return response()->json(["message"=>$message,"code"=>$code], $code);
    }
    public static function returnValidationResponse($errors,$code=422){
        return response()->json(["errors"=>$errors,"code"=>$code], $code);
    }
}
