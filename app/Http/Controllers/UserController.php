<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;

class UserController extends Controller
{
    public function register(Request $request)
    {
        $validation = Validator::make($request->all(), [
            'name' => 'required|string',
            'email' => 'required|email|unique:users',
            'password' => 'required|min:6',
        ]);
        if ($validation->fails()) {
            return response()->json($validation->errors(), 422);
        }
        try{
            $allData = $request->all();
            $allData['password'] = bcrypt($allData['password']);
            $newUser = User::create($allData);
            $newUser->attachRole("voter");
            $tokenStr = $newUser->createToken('api-application')->accessToken;
            $resArr["token"] = $tokenStr;
            $resArr["status code"] = 201;
            return response()->json($resArr, 201);
        }catch (\Exception $e){
            return response()->json(["error"=>$e->getMessage()], 400);

        }

    }

    public function login(Request $request)
    {
        if (Auth::attempt([
            'email' => $request->email,
            'password' => $request->password,
        ])) {
            $user = Auth::user();
            $resArr = [];
            $resArr['token'] = $user->createToken('api-application')->accessToken;
            $resArr['name'] = $user->username;
            return response()->json($resArr, 200);
        } else {
            return response()->json(['error' => 'Unauthorized access','status code'=>401], 401);
        }
    }
    public function index()
    {
     // echo auth()->user()->with("roles");die;
      $deatails=auth()->user();
        if($deatails['avatar']==""){
            $deatails['avatar']="place_holder.jpg" ;
        }
        if(auth()->user()->hasRole("organizer")){
            $deatails['role']='organizer';
        }else{
            $deatails['role']='voter';
        }
        return response()->json(['data'=>$deatails,'message'=>'success'],200);
    }
    public function updateAvatar(Request $request){
        try{
            $validation = Validator::make(
                $request->all(), [
                'file'=>'required',
                'file.*' => 'required|mimes:jpg,jpeg,png,bmp|max:20000',
            ],[
                    'file.*.required' => 'Please upload an image',
                    'file.*.mimes' => 'Only jpeg,png and bmp images are allowed',
                    'file.*.max' => 'Sorry! Maximum allowed size for an image is 20MB',
                ]
            );
            if($validation->fails()) {
                return response()->json($validation->errors(), 422);
            }
            $id= auth()->user()['id'];
            $response = cloudinary()->upload($request->file('file')->getRealPath(),[
                'folder'=> 'myballot/users/'.$id.'/',
                'public_id'=>'avatar'.$id,
                'overwrite'=>true,
                'format'=>"webp"
            ])->getSecurePath();
            User::where('id',$id)->update(['avatar'=>$response]);
            $data = ['message' => 'profile avatar updated successfully','data'=>$response,'response code'=>201];
            return response()->json($data,201);
        }catch (Exception $e){
            $resArr["status code"] = 400;
            return response()->json($resArr, 400);

        }

    }

    /**
     * Logout user (Revoke the token)
     *
     * @return [string] message
     */
    public function logout(Request $request)
    {
        $request->user()->token()->revoke();
        return response()->json([
            'message' => 'Successfully logged out'
        ]);
    }
}
