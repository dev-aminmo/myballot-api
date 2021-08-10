<?php

namespace App\Http\Controllers\PluralityElection;

use App\Http\Controllers\Controller;
use App\Http\Requests\PluralityElection\CreatePluralityElectionRequest;
use App\Http\Requests\PluralityElection\VotePluralityElectionRequest;
use App\Models\Candidate;
use App\Models\Ballot;
use App\Models\PluralityCandidate;
use App\Models\ListCandidate;
use App\Models\Party;
use App\Models\PluralityElection\PluralityElection;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use App\Helpers\MyHelper;
use App\Helpers\MyResponse;

class PluralityElectionController extends Controller
{
    use MyHelper;
    use MyResponse;

    function create(CreatePluralityElectionRequest $request)
    {
        $id = auth()->user()['id'];
        $allData = $request->all();
        $allData['organizer_id'] = $id;
        $allData['type'] = 1;
        $ballot_id = Ballot::create($allData)->id;
        PluralityElection::create([
            'id' => $ballot_id,
            'seats_number' => (!empty($request->seats_number)) ? $request->seats_number : 1,
        ]);

        foreach ($request->candidates as $candidate) {
            $candidate_id = Candidate::create(['name' => $candidate['name'],
                'description' => (!empty($candidate['description'])) ? $candidate['description'] : null,
                "type" => 1
            ])->id;
            PluralityCandidate::create([
                    "id" => $candidate_id,
                    "election_id" => $ballot_id
                ]
            );
        }

        return $this->returnSuccessResponse('election created successfully');
    }

    function vote(VotePluralityElectionRequest $request)
    {
        $ballot_id = $request->ballot_id;
        $user = auth()->user();
        $is_voter = $user->ballots()->where('ballot_id', $ballot_id)->first();
        $candidate = PluralityCandidate::find($request->candidate_id);
        $candidate = $candidate->candidate;
        if (!empty($candidate) && $is_voter) {
            $voted = $is_voter->pivot->voted;
            if ($voted) {
                return $this->returnErrorResponse('vote already casted');
            }
            $candidate->update(['count' => DB::raw('count+1'),]);
            $user->ballots()->updateExistingPivot($ballot_id, ['voted' => true]);
            return $this->returnSuccessResponse('vote casted successfully');
        } else {
            return redirect('/');
        }
    }

    function results(Request $request)
    {

        $request->merge(['id' => $request->route('id')]);
        $validation = Validator::make($request->all(), [
            'id' => 'required|integer|exists:ballots,id',
        ]);
        if ($validation->fails()) {
            return response()->json($validation->errors(), 422);
        }
        $election = Ballot::where('id', $request->id)->first();
        $data["candidates"] = PluralityCandidate::where('election_id', $request->id)->with('candidate')->get()->pluck("candidate")->sortByDesc('count')->values();
        $election_id = $election->id;
        $seats_number = PluralityElection::find($election_id)->seats_number;
        $data["candidates"]->each(function ($candidate) use (&$seats_number) {
            $candidate->makeVisible("count");
            if ($seats_number > 0) {
                $candidate->selected = true;
                $seats_number--;
            } else {
                $candidate->selected = false;
            }
            return $candidate;

        });

        $data["added_voters"] = $election->users()->where('ballot_id', $election_id)->count();
        $data["vote_casted"] = $election->users()->where(['ballot_id' => $election_id,
            'voted' => true
        ])->count();
        $data["vote_ratio"] = ($data["added_voters"] == 0) ? 0 : $data["vote_casted"] / $data["added_voters"];
        $data["vote_ratio"] = (float)number_format((float)$data["vote_ratio"], 2, '.', '');
        $data["code"] = 200;
        return Response()->json($data, 200);


    }
}
