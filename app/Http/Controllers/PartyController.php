<?php

namespace App\Http\Controllers;

use App\Models\Party;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class PartyController extends Controller
{
    //

    function create(Request $request)
    {
        $validation =  Validator::make($request->all(), [
            "name"=>"required|string|min:2|max:191",
            "description"=>"required|string",
            "picture"=>"string|max:255",
            "party_id"=>'integer|min:1'
        ]);
        if ($validation->fails()) {
            return response()->json($validation->errors(), 422);
        }
        $allData = $request->all();
      //  $allData['organizer_id']=$id;
        Party::create($allData);
    }

    public function update(Request $request){
        try{
            $validation = Validator::make(
                $request->all(), [
                'data'=>'required',
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
            $resArr["status code"] = 200;
            return response()->json($resArr, 200);

        }

    }

}
