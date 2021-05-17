<?php

namespace App\Http\Controllers\PluralityElection;

use App\Http\Controllers\Controller;
use App\Mail\YouAreInvited;
use App\Models\Candidate;
use App\Models\Election;
use App\Models\FreeCandidate;
use App\Models\PartisanCandidate;
use App\Models\Party;
use App\Models\PluralityElection\PluralityElection;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;
use App\Mail\MyTestMail;
use Illuminate\Validation\Rule;

class PluralityElectionController extends Controller
{

    function create(Request $request){
        $validation =  Validator::make($request->all(), [
            'start_date'    => 'required|date|date_format:Y-m-d H:i|after_or_equal:now',
            'end_date'      => 'required|date|date_format:Y-m-d H:i|after:start_date',
            'title'=> 'required|string|min:2|max:255',
            'description'=> 'string|min:10|max:400',
            'parties' => 'array|min:1|max:30',
            'parties.*.name'=>'required',
            'parties.*.candidates' => 'required|array|min:1|max:1',
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
        if ($candidates_count<2) {
            return response()->json([
                'message'=>"the minimum number of candidates is 2",
                'code'=>'422'] ,422);
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
            $election_id=Election::create($allData)->id;
            PluralityElection::create([
                'id'=>$election_id
            ]);

            if (!empty($request->parties)) {
                foreach($request->parties as $party){
                    $party_id = Party::create(['name'=> $party['name'],
                        'election_id'=>$election_id])->id;
                    foreach($party['candidates']as $candidate){
                       $candidate_id= Candidate::create([ 'name'=> $candidate['name'],
                           'description'=>(!empty($candidate['description'])) ? $candidate['description'] : null,
                           "election_id"=>$election_id
                       ])->id;
                        PartisanCandidate::create([
                                 'id'=>$candidate_id,
                                'party_id'=>$party_id,
                            ]
                        );
                    }
                }
            }
            if (!empty($request->free_candidates)) {
                foreach($request->free_candidates as $candidate){
                    $candidate_id= Candidate::create([ 'name'=> $candidate['name'],
                        'description'=>(!empty($candidate['description'])) ? $candidate['description'] : null,
                        "election_id"=>$election_id
                    ])->id;
                    FreeCandidate::create([
                          "id"=>$candidate_id
                        ]
                    );
                }
            }
            $data = ['message' => 'election created successfully','code'=>201];
            return Response()->json($data,201);
        }catch ( \Exception  $exception){
            throw
            $response['error']=$exception;
            return response()->json($exception->getTrace(), 400);
        }
    }


    function add_party(Request $request){
        $validation =  Validator::make($request->all(), [
            'name'=>'required',
            'candidates' => 'required|array|min:1|max:1',
            'candidates.*.name' => 'required|string|min:4|max:255',
            'candidates.*.description' => 'string|min:4|max:400',
            'election_id'=>'required|integer|exists:plurality_elections,id'
        ]);
        if ($validation->fails()) {
            return response()->json($validation->errors(), 422);
        }
        $election_id= $request->election_id;
        if ($this->isStarted($election_id) || !$this->isOrganizer($election_id)) return  redirect('/');
        $party_id=Party::create(['name'=> $request->name,
            'election_id'=> $election_id])->id;
        foreach( $request->candidates as $candidate){
            PartisanCandidate::create([
                'name'=> $candidate['name'],
                'description'=>(!empty($candidate['description'])) ? $candidate['description'] : null,
                'party_id'=>$party_id,
            ]
        );}
        $data = ['message' => 'party added successfully','code'=>201];
        return Response()->json($data,201);
    }
    function update_party(Request $request){
        $validation =  Validator::make($request->all(), [
            'id'=>'required|integer|exists:parties,id',
            'name'=>'required|string|min:4|max:255',
        ]);
        if ($validation->fails()) {
            return response()->json($validation->errors(), 422);
        }
        $id= $request->id;
       $party=Party::where('id',$id)->first();
        if ($this->isStarted($party->election_id) || !$this->isOrganizer($party->election_id)) return  redirect('/');
        $party->name=$request->name;
        $party->save();
        $data = ['message' => 'party updated successfully','code'=>201];
        return Response()->json($data,201);
    }
    function delete_party(Request $request){
        $validation =  Validator::make($request->all(), [
            'id'=>'required|integer|exists:parties,id']);
        if ($validation->fails()){
            return response()->json($validation->errors(), 422);
        }
        $id= $request->id;
       $party=Party::where('id',$id)->first();
        if ($this->isStarted($party->election_id) ||!$this->isOrganizer($party->election_id)) return  redirect('/');
        $party->delete();
        $data = ['message' => 'party deleted successfully','code'=>201];
        return Response()->json($data,201);
    }
    function update_candidate(Request $request){
        $validation =  Validator::make($request->all(), [
            'id'=>'required|integer|exists:partisan_candidates,id',
            'name' => 'string|min:4|max:255',
            'description' => 'string|min:4|max:400',
            ]);
        if ($validation->fails()){
            return response()->json($validation->errors(), 422);
        }
     try{
        $id= $request->id;
        $candidate=PartisanCandidate::where('id',$id)->first();
        $party=Party::where('id',$candidate->id)->first();
        if ($this->isStarted($party->election_id) || !$this->isOrganizer($party->election_id)) return  redirect('/');
        $candidate->update($request->only(['name', 'description']));
        $data = ['message' => 'candidate updated successfully','code'=>201];
        return Response()->json($data,201);
        }catch ( \Exception  $exception){
            $response=['message'=>"error has occurred",
                "code"=>"400"];
            return response()->json($response, 400);
            }
    }


    function add_voters(Request $request)
    {
        $validation = Validator::make($request->all(), [
            'election_id' => 'required|integer|exists:elections,id',
            'emails' => 'required|array|min:1|max:150',
            'emails.*' => 'email',
                ]);
        if ($validation->fails()) {
            return response()->json($validation->errors(), 422);
        }
        if ($this->isStarted($request->election_id) ||!$this->isOrganizer($request->election_id)) return  redirect('/');

        try {
            $organizers = User::whereRoleIs(['organizer'])->get();
            $intersection = $organizers->intersect(User::whereIn('email', $request->emails)->get());

            if (count($intersection)) {
                $ad = [];
                $intersection->each(function ($org) use (&$ad) {
                    $ad[] = $org->email;
                    return $org->email;
                });
                return response()->json([
                    "message" => "organizers cannot vote",
                    "emails" => $ad,
                    "code" => 422
                ], 422);
            }
            foreach ($request->emails as $email) {
                $u = User::where('email', $email)->first();

                if (!empty($u)) {

                    $u->elections()->syncWithoutDetaching($request->election_id);


                    $details = [
                        'link' => "https://www.google.com/",
                    ];
                  //  Mail::to($email)->send(new YouAreInvited($details));

                } else {
                    $pass = Str::random(6);
                    $user = User::create([
                            'email' => $email,
                            'password' => bcrypt($pass),]
                    );
                    $user->attachRole('voter');
                    $user->elections()->attach($request->election_id);
                    $details = [
                        'email' => $email,
                        'password' => $pass
                    ];

                    Mail::to($email)->send(new MyTestMail($details));

                }
                $data = ['message' => 'voters added successfully','code'=>201];
                return Response()->json($data,201);
            }
        } catch (\Exception  $exception) {
            $response = ['message' => "error has occurred",
                "code" => "400"];
            return response()->json($exception->getTrace(), 400);
        }
    }

    function vote(Request $request){

        $validation =  Validator::make($request->all(), [
            'election_id'=>'required|integer|exists:elections,id',
            'candidate_id'=>'required|integer|exists:candidates,id'
        ]);
        if ($validation->fails()) {
            return response()->json($validation->errors(), 422);
        }



        $election_id= $request->election_id;


        if (!$this->isStarted($election_id)|| $this->isEnded($election_id)) return  redirect('/');

        $user_id=auth()->user()['id'];
        $user=User::where("id",$user_id)->first();
        $is_voter =  $user->elections()->where('election_id', $election_id)->first();
        $candidate=Candidate::where([['id',$request->candidate_id], ['election_id', $election_id],])->first();
        if(!empty($candidate) &&$is_voter){
            $voted =   $is_voter->pivot->voted;
            if( $voted){
              $data = ['message' => 'vote already casted','code'=>422];
              return response()->json($data, 422);
           }
      $candidate->update(['count'=> DB::raw('count+1'),]);

            $user->elections()->updateExistingPivot($election_id, ['voted'=>true]);
        $data = ['message' => 'vote casted successfully','code'=>201];
        return Response()->json($data,201);
        }else{
            return  redirect('/');
        }
        }

    public function isOrganizer($election_id)
    {
        $p=Election::where('id',$election_id)->first();
        if(empty($p)){
            return false;
        }
        if($p->organizer_id == auth()->user()['id']){
            return true;
        }
        return false;
    }
    public function isStarted($election_id){

        $election=Election::where('id',$election_id)->first();
        if(empty($election)){
            return false;
        }
        $start = Carbon::parse($election->start_date);
        $before=Carbon::now()->isBefore($start);
        if($before){
            return false;
        }
        return true;
    }
    public function isEnded($election_id){
        $election=Election::where('id',$election_id)->first();
        if(empty($election)){
            return false;
        }
        $end = Carbon::parse($election->end_date);
        $ended=Carbon::now()->isAfter($end);
        if($ended){
            return true;
        }
        return false;
    }
}
