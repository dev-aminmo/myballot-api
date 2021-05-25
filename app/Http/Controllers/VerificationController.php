<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;

class VerificationController extends Controller
{
    //
    public function verify(Request $request){
        if(!$request->hasValidSignature()){
            return redirect("/");
        }
        $request->merge(['id' => $request->route('id')]);

        $user=User::findOrFail($request->id);

        if (!$user->hasVerifiedEmail()){
            $user->markEmailAsVerified();
        }
        return  response()->json("its all ok",200);

    }
    public function resend(){
        if (auth()->user()->hasVerifiedEmail()){
            return redirect("/");
        }
        auth()->user()->sendEmailVerificationNotification();
        return  response()->json("its all ok",200);
    }
}
