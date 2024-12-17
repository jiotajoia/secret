<?php

namespace App\Http\Controllers\Secret;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class SecretController extends Controller
{
   
    public function createGroup(Request $request){
        $group = Group::create([
            'name' => $request->name,
        ]);

        return response()->json($group, 201);
    }

    public function addParticipant(Request $request, Group $group){
        $participant = $group->participants()->create([
            'name' => $request->name,
        ]);

        return response()->json($participant, 201);
    }

    public function generateMatches(Group $group)
    {
        $participants = $group->participants()->pluck('id')->shuffle();

        if ($participants->count() < 2) {
            return response()->json(['error' => 'número inválido de participante: mínimo 2'], 400);
        }

        $matches = $participants->zip($participants->skip(1)->push($participants->first()));

        $matches->each(function ($pair) use ($group) {
            $giver = $pair[0];
            $receiver = $pair[1];
        
            $participant = $group->participants->find($giver);
            if ($participant) {
                $participant->update(['match_id' => $receiver]);
            }
        });

        return response()->json(['message' => 'Matches gerados com sucesso!']);
    }

}
