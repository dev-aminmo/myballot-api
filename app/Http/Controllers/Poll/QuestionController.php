<?php

namespace App\Http\Controllers\Poll;

use App\Helpers\MyResponse;
use App\Http\Controllers\Controller;
use App\Http\Requests\AddQuestionRequest;
use App\Http\Requests\DeleteQuestionRequest;
use App\Http\Requests\UpdateQuestionRequest;
use App\Models\Ballot;
use App\Models\Poll\Answer;
use App\Models\Poll\Poll;
use App\Models\Poll\Question;
use Illuminate\Http\Request;

class QuestionController extends Controller
{
    use MyResponse;

public function add(AddQuestionRequest $request){
    try {
        $poll_id=$request->ballot_id;
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
        $data = ['message' => 'questions added successfully', 'code' => 201];
        return Response()->json($data, 201);
    } catch (\Exception  $exception) {
        throw $exception;
        $response['error'] = $exception;
        return response()->json($exception->getTrace(), 400);
    }
}
public function  update(UpdateQuestionRequest $request){
        try{
           $question= Question::find($request->question_id);
           if(!empty($request->value)){
               $question->value=$request->value;
           }
           if(!empty($request->type)){
               $question->type_id=$request->type;

           }
           $question->save();
            return $this->returnSuccessResponse('question updated successfully');
        }catch ( \Exception  $exception){
            return $this->returnErrorResponse();
        }
    }
    public function  delete(DeleteQuestionRequest $request){
        try{
            $request->question->delete();


            return $this->returnSuccessResponse('question deleted successfully');
        }catch ( \Exception  $exception){
            return $this->returnErrorResponse();
        }

}
}
