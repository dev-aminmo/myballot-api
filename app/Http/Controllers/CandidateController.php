<?php

namespace App\Http\Controllers;

use App\Models\Candidate;
use App\Models\Party;
use Illuminate\Http\Request;

class CandidateController extends Controller
{
    //
    function create(Request $request)
    {
        $allData = $request->all();
        Candidate::create($allData);
    }
}
