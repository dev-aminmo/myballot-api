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
            'timezone'=> 'integer|min:-12|max:+14',
        ]);
        if ($validation->fails()) {
            return response()->json($validation->errors(), 422);
        }
            $todayDate =Carbon::now();
        $todayDate = $todayDate->setTimezone($request->timezone);
echo $todayDate;
        $validation =  Validator::make($request->all(), [
            'timezone'=> 'integer|min:-12|max:+14',
            'start_date'    => 'required|date|date_format:Y-m-d H:i|after_or_equal:'.$todayDate,
            'end_date'      => 'required|date|date_format:Y-m-d H:i|after:start_date',
            'title'=> 'required|string|min:2|max:255',
            'description'=> 'string|min:10|max:400',
            'parties' => 'array|min:1|max:30',
            'parties.*.name'=>'required',
            'parties.*.candidates' => 'required|array|max:30',
            'parties.*.candidates.*.name' => 'required|string|min:4|max:255',
            'parties.*.candidates.*.description' => 'string|min:4|max:400',
            'free_candidates'=>'array|min:1|max:30',
            'free_candidates.*.name' => 'required|string|min:4|max:255',
            'free_candidates.*.description' => 'string|min:4|max:400',
        ]);
        if ($validation->fails()) {
            return response()->json($validation->errors(), 422);
        }
       $candidates_count=0;
        if (!empty($request->free_candidates)) {
            $candidates_count +=count($request->free_candidates);
        }
        if (!empty($request->parties)) {
            foreach($request->parties as $party){
                $candidates_count += count(  $party['candidates']);
            }
        }
        echo ($candidates_count);
        if ($candidates_count<2) {
            return response()->json([
                'message'=>"the minimum number of candidates is 2",
                    'code'=>'422'] ,422);
        }
        //echo (count($request->free_candidates));
        die;
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
