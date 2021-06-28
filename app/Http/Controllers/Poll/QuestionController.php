<?php

namespace App\Http\Controllers\Poll;

use App\Http\Controllers\Controller;
use App\Http\Requests\AddQuestionRequest;
use App\Models\Election;
use App\Models\Poll\Answer;
use App\Models\Poll\Poll;
use App\Models\Poll\Question;
use Illuminate\Http\Request;

class QuestionController extends Controller
{

public function add(AddQuestionRequest $request){
    try {
        $poll_id=$request->election_id;
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
}
