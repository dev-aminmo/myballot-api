<?php

namespace App\Http\Controllers\Poll;

use App\Helpers\MyResponse;
use App\Http\Controllers\Controller;
use App\Http\Requests\ResultRequest;
use App\Http\Requests\VotePollRequest;
use App\Models\Ballot;
use App\Models\Poll\Answer;
use App\Models\Poll\Poll;
use App\Models\Poll\Question;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

class PollController extends Controller
{
    use MyResponse;
    //
    function create(Request $request)
    {
        $validation = Validator::make($request->all(), [
            'start_date' => 'required|date|date_format:Y-m-d H:i|after_or_equal:now',
            'end_date' => 'required|date|date_format:Y-m-d H:i|after:start_date',
            'title' => 'required|string|min:2|max:255',
            'description' => 'string|min:10|max:400',
            'questions' => 'required|array|min:2|max:60',
            'questions.*.value' => 'required|string|min:10|max:400',
            'questions.*.type' => 'required|integer|min:1|max:2',
            'questions.*.answers' => 'required|array|min:2|max:20',
            'questions.*.answers.*.value' => 'required|string|min:2|max:255',
        ]);
        if ($validation->fails()) {
            return response()->json($validation->errors(), 422);
        }
        $start = Carbon::parse($request->start_date);
        $end = Carbon::parse($request->end_date);
        $diff_in_minutes = $end->diffInMinutes($start);
        if ($diff_in_minutes < 5) {
            return response()->json([
                "message" => "the difference between start_date and end_date should be more than 5 minutes",
                "code" => "202"
            ], 422);
        }
        try {
            $id = auth()->user()['id'];
            $allData = $request->all();
            $allData['organizer_id'] = $id;
            $allData['type']=3;

            $poll_id=Ballot::create($allData)->id;


            foreach ($request->questions as $question) {
                $question_id = Question::create([
                    'value' => $question['value'],
                    'type_id' => $question['type'],
                    'poll_id' => $poll_id])->id;
                foreach ($question['answers'] as $answer) {
                    Answer::create([
                            'value' => $answer['value'],
                            'question_id' => $question_id
                        ]
                    );
                }
            }



            return $this->returnSuccessResponse('poll created  successfully',201);

        }catch ( \Exception  $exception){
            return $this->returnErrorResponse();
        }

    }
    function get(Request $request){
        $request->merge(['ballot_id' => $request->route('id')]);
        $validation = Validator::make($request->all(), [
            'ballot_id'=>'required|integer|exists:ballots,id'
            ,]);
        if ($validation->fails()) {
            return response()->json($validation->errors(), 422);
        }
       $id= $request->ballot_id;
        $data=Question::where("poll_id",$id)->with("answers")->get();
        return $this->returnDataResponse($data);

    }
    function vote(VotePollRequest $request)
    {
        $ballot_id=$request->ballot_id;
        $user=auth()->user();
        $is_voter =  $user->ballots()->where('ballot_id', $ballot_id)->first();
        if($is_voter){
            $voted =   $is_voter->pivot->voted;
            if( $voted){
                return  $this->returnErrorResponse('vote already casted');
            }
        $collection = collect($request->votes);
        $collection= $collection->sortBy("question_id")->values();
        $data = Question::where("poll_id", $ballot_id)->with("answers")->orderBy("id")->get();
        if (count($collection) == count($data)) {
            $errors=null;
            $collection->each(function ($question)use(&$data,&$ballot_id,&$errors) {
                $db_question=$data->find($question["question_id"]);
                if(empty($db_question)){
                    $errors[]="all the questions should belong to the same ballot";
                    return false;
                }else{
                        if (count($question["answers"]) > 1 && $db_question->type_id == 1) {
                            $errors[]= "question with type 1 must choose only one answer";
                            return false;
                        } else {
                            foreach ($question["answers"] as $answer_id => $v) {
                                $answer = Answer::find($v);
                                if ($answer->question_id != $question["question_id"]) {
                                    $errors[] = "answer ->qst id not equal to original qst id ";
                                    return false;
                                }
                                if ($answer->question->poll_id != $ballot_id) {
                                    $errors[] = "answer ballot id not equal to original ballot id ";

                                    return false;
                                }
                            }
                        }
                }
                });
            if(@count($errors)<=0){
                //Cast Vote
                $collection->each(function ($question)use(&$ballot_id){
                    foreach ($question["answers"] as $answer_id => $v) {
                        Answer::find($v)->update(['count'=> DB::raw('count+1'),]);
                        Auth::user()->ballots()->updateExistingPivot($ballot_id, ['voted'=>true]);
                    }
                    });
                return  $this->returnSuccessResponse('vote casted successfully');
            }else{
                return $this->returnErrorResponse($errors);
            }
        } else {
            return $this->returnErrorResponse("the number of questions doesn't match with the actual number of questions in the database");

        }
    }else{
            return  redirect('/');

        }
    }
    function results(ResultRequest $request){
        $request->merge(['id' => $request->route('id')]);
        $validation =  Validator::make($request->all(), [
            'id'=>'required|integer|exists:ballots,id',
        ]);
        if ($validation->fails()) {
            return response()->json($validation->errors(), 422);
        }
        $ballot= Ballot::where('id',$request->id)->first();
        $data["added_voters"] =$ballot->users()->where('ballot_id',$ballot->id )->count();
        $data["vote_casted"] =$ballot->users()->where(['ballot_id'=>$ballot->id ,
            'voted'=>true
        ])->count();
        $data["vote_ratio"] =  ( $data["added_voters"] == 0)?0: $data["vote_casted"]/  $data["added_voters"];
        $data["vote_ratio"] =(float) number_format((float) $data["vote_ratio"], 2, '.', '');

        $data["questions"]=Question::where("poll_id",$request->id)->with("answers")->get();

        return $this->returnDataResponse($data);


    }
}
