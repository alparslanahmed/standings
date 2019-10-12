<?php

namespace App\Http\Controllers;

use App\Country;
use App\League;
use App\Standing;
use Illuminate\Http\Request;

class StandingsController extends Controller
{
    public function countries()
    {
        $countries = Country::all();
        return response()->json($countries);
    }

    public function leagues(Request $request)
    {
        $request->validate([
            'country_id' => 'required'
        ]);

        $leagues = League::where('country_id', $request->get('country_id'))->get();

        return response()->json($leagues);
    }

    public function standings(Request $request)
    {
        $request->validate([
            'league_id' => 'required'
        ]);

        $standings = Standing::where('league_id', $request->get('league_id'))->orderBy('pts', 'DESC')->get();

        return response()->json($standings);
    }
}
