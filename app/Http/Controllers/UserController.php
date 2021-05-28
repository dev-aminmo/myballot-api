<?php

namespace App\Http\Controllers;

use App\Helpers\MyResponse;
use App\Http\Requests\User\LoginPostRequest;
use App\Http\Requests\User\RegisterPostRequest;
use App\Http\Requests\User\UpdateUserAvatarRequest;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class UserController extends Controller
{
    use MyResponse;
    public function register(RegisterPostRequest $request)
    {
        try{

            //$newUser = User::create($request->getAttributes())->SendEmailVerificationNotification();
            $newUser = User::create($request->getAttributes());
            $newUser->attachRole("organizer");
            $tokenStr = $newUser->createToken('api-application')->accessToken;
            $resArr["token"] = $tokenStr;
            return $this->returnDataResponse($resArr,201);
        }catch (\Exception $e){
            return  $this->returnErrorResponse();
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
            return $this->returnDataResponse($data,201);

        } else {
            $this->returnValidationResponse(["Incorrect email or password"],422);
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
        return $this->returnDataResponse($data,200);

    }
    public function updateAvatar(UpdateUserAvatarRequest $request){
        try{
            $user= auth()->user();
            $response = cloudinary()->upload($request->file('file')->getRealPath(),[
                'folder'=> 'myballot/users/'.$user->id.'/',
                'public_id'=>'avatar'.$user->id,
                'overwrite'=>true,
                'format'=>"webp"
            ])->getSecurePath();
            $user->update(['avatar'=>$response]);
            dd($user->id);
            return $this->returnSuccessResponse('profile avatar updated successfully',201);
          }catch (Exception $e){
            return $this->returnErrorResponse();
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
       return $this->returnSuccessResponse('Successfully logged out');

    }
}
