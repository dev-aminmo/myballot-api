<?php

namespace App\Http\Controllers;

use App\Http\Requests\LoginPostRequest;
use App\Http\Requests\RegisterPostRequest;
use App\Http\Requests\UpdateUserAvatarRequest;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;

class UserController extends Controller
{
    public function register(RegisterPostRequest $request)
    {
        try{
            $newUser = User::create($request->getAttributes());
            $newUser->attachRole("organizer");
            $tokenStr = $newUser->createToken('api-application')->accessToken;
            $resArr["token"] = $tokenStr;
            $resArr["status code"] = 201;
            return response()->json($resArr, 201);
        }catch (\Exception $e){
            return response()->json(["error"=>$e->getMessage()], 400);

        }

    }

    public function login(LoginPostRequest $request)
    {
        if (Auth::attempt([
            'email' => $request->email,
            'password' => $request->password,
        ])) {
            $user = Auth::user();
            $data = [];
            $data['token'] = $user->createToken('api-application')->accessToken;
            $data['code'] = 200;
            return response()->json($data, 200);
        } else {
            return response()->json(['error' => 'Unauthorized access','code'=>401], 401);
        }
    }
    public function index()
    {
      $data=auth()->user();
        if($data['avatar']==""){
            $data['avatar']="place_holder.jpg" ;
        }
        if(auth()->user()->hasRole("organizer")){
            $data['role']='organizer';
        }else{
            $data['role']='voter';
        }
        return response()->json(['data'=>$data,'message'=>'success'],200);
    }
    public function updateAvatar(UpdateUserAvatarRequest $request){
        try{
            $id= auth()->user()['id'];
            $response = cloudinary()->upload($request->file('file')->getRealPath(),[
                'folder'=> 'myballot/users/'.$id.'/',
                'public_id'=>'avatar'.$id,
                'overwrite'=>true,
                'format'=>"webp"
            ])->getSecurePath();
            User::where('id',$id)->update(['avatar'=>$response]);
            $data = ['message' => 'profile avatar updated successfully','data'=>$response,'code'=>201];
            return response()->json($data,201);
        }catch (Exception $e){
            $resArr["code"] = 400;
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
