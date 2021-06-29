<?php

namespace App\Http\Controllers\Poll;

use App\Helpers\MyResponse;
use App\Http\Controllers\Controller;
use App\Http\Requests\VotePollRequest;
use App\Models\Election;
use App\Models\Poll\Answer;
use App\Models\Poll\Poll;
use App\Models\Poll\Question;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
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
            'questions.*.answers.*.value' => 'required|string|min:4|max:255',
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
            $allData['type']="poll";

            $poll_id=Election::create($allData)->id;
             Poll::create(["id" =>$poll_id]);

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
        $request->merge(['election_id' => $request->route('id')]);
        $validation = Validator::make($request->all(), [
            'election_id'=>'required|integer|exists:polls,id'
            ,]);
        if ($validation->fails()) {
            return response()->json($validation->errors(), 422);
        }
       $id= $request->election_id;
        $election=Election::find($id);
        $data=Question::where("poll_id",$id)->with("answers")->get();
        return $this->returnDataResponse($data);

    }
    function vote(VotePollRequest $request){

    }
}
