<?php

namespace App\Http\Controllers;

use App\Helpers\MyResponse;
use App\Http\Requests\User\LoginPostRequest;
use App\Http\Requests\User\RegisterPostRequest;
use App\Http\Requests\User\UpdateUserAvatarRequest;
use App\Models\User;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class UserController extends Controller
{
    use MyResponse;
    public function register(RegisterPostRequest $request)
    {
        try{

            //$newUser = User::create($request->getAttributes())->SendEmailVerificationNotification();
            $data=$request->getAttributes();
            $data['is_organizer']=true;
            $newUser = User::create($data);
            //$newUser->attachRole("organizer");
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
            return $this->returnValidationResponse(["Incorrect email or password"],401);
        }
    }
    public function index()
    {
      $data=auth()->user();
      return $this->returnDataResponse($data,200);
    }
    public function updateProfile(Request $request){
     $user=   auth()->user();
        $validation = \Illuminate\Support\Facades\Validator::make($request->all(), [
            'email' => 'unique:users,email',
            'name' => 'string|min:4|max:400',
        ]);
        if ($validation->fails()) {
            return $this->returnValidationResponse ( $validation->errors(),422,'Validation error');

        }
        if(isset($request->email)){
            $user->email=$request->email;
        }
        if(isset($request->name)){
            $user->name=$request->name;
        }
        $user->save();
        return $this->returnSuccessResponse('profile updated successfully',201);

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
