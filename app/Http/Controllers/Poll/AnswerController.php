<?php

namespace App\Http\Controllers\Poll;

use App\Helpers\MyResponse;
use App\Http\Controllers\Controller;
use App\Http\Requests\AddAnswerRequest;
use App\Http\Requests\DeleteAnswerRequest;
use App\Http\Requests\UpdateAnswerRequest;
use App\Models\Poll\Answer;
use Illuminate\Http\Request;

class AnswerController extends Controller
{
    use MyResponse;
  public function  add(AddAnswerRequest $request){
      try{
      Answer::create([
              'value' => $request->value,
              'question_id' => $request->question_id
          ]
      );
      return $this->returnSuccessResponse('answer added successfully');
      }catch ( \Exception  $exception){
          return $this->returnErrorResponse();
      }
      }
  public function  update(UpdateAnswerRequest $request){
      try{
          Answer::find($request->answer_id)->update([
              "value"=>$request->value,
          ]);

      return $this->returnSuccessResponse('answer updated successfully');
      }catch ( \Exception  $exception){
          return $this->returnErrorResponse();
      }
      }
  public function  delete(DeleteAnswerRequest $request){
      try{
          $request->answer->delete();

        //  Answer::find($request->answer_id)->delete();

      return $this->returnSuccessResponse('answer deleted successfully');
      }catch ( \Exception  $exception){
          return $this->returnErrorResponse();
      }
      }
}
