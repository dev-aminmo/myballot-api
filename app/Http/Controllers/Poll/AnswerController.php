<?php

namespace App\Http\Controllers\Poll;

use App\Helpers\MyResponse;
use App\Http\Controllers\Controller;
use App\Http\Requests\AddAnswerRequest;
use App\Models\Poll\Answer;
use Illuminate\Http\Request;

class AnswerController extends Controller
{
    use MyResponse;
  public function  add(AddAnswerRequest $request){
      try{
      $id=Answer::create([
              'value' => $request->value,
              'question_id' => $request->question_id
          ]
      )->id;

      return $this->returnSuccessResponse('answer added successfully',201,$id);

      }catch ( \Exception  $exception){
          return $this->returnErrorResponse();
      }
      }
}
