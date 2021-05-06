<?php

namespace App\Http\Controllers;

use App\Models\Election;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Validator;

class ElectionController extends Controller
{
    function create(Request $request){
        $validation =  Validator::make($request->all(), [
            'start_date'    => 'required|date|date_format:Y-m-d H:i|after_or_equal:today',
            'end_date'      => 'required|date|date_format:Y-m-d H:i|after:start_date',
            'title'=> 'required|string|min:2|max:255',
            'description'=> 'string|min:10|max:400',
        ]);
        if ($validation->fails()) {
            return response()->json($validation->errors(), 422);
        }
            $start = Carbon::parse($request->start_date);
            $end = Carbon::parse($request->end_date);
        $diff_in_minutes = $end->diffInMinutes($start);
        if ($diff_in_minutes < 5)  {
            return response()->json([
                "message"=>"the difference between start_date and end_date should be more than 5 minutes",
                "code"=>"202"
            ], 422);
        }
        try{
            $id= auth()->user()['id'];
            $allData = $request->all();
            $allData['organizer_id']=$id;
            Election::create($allData);
            $data = ['message' => 'election created successfully'];
            return Response()->json($data,201);
        }catch ( \Exception  $exception){
            $response['error']="an error has occured";
            return response()->json($response, 400);
        }
    }
}
