<?php

namespace App\Http\Controllers\Secret;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Group;
use App\Models\User;
use App\Models\Participant;

class SecretController extends Controller
{

    public function getGroups(){
        $user = auth()->user();

        $groups = $user->groups;
        $title = 'Grupos';

        return view('', compact('groups', 'title'));
    }

    public function createGroup(){
        $title = 'Criar Grupo';
        return view('', compact('title'));

    }
   
    public function storeGroup(Request $request){
        $user = auth()->user();

        $group = Group::create([
            'name' => $request->name,
            'user_id' => $user->id,
        ]);

        //return response()->json($group, 201);

        return redirect()->route('groups')->with('success', 'Grupo criado com sucesso');
    }

    public function showGroup(Group $group){
        $title = 'Grupo';

        return redirect()->route('groups.show', ['group' => $group->id]);

    }

    public function editGroup(Request $request, Group $group){
        $title = 'Editar Grupo';

        return view('', compact('title', 'group'));
    }

    public function updateGroup(Request $request, Group $group){
        $request->validate([
            'name' => 'required|string|max:255',
        ]);

        $user = auth()->user();

        $owner_id = $group->user_id;

        if($user->id != $owner_id){
            return redirect()->route('groups.group.participants', $group->id)
            ->with('error', 'O usuário não é dono do grupo');
        }

        $group->update([
            'name' => $request->name,
        ]);

        return redirect()->route('groups')->with('success', 'Grupo editado com sucesso');
    }

    public function destroyGroup(Request $request, Group $group){
        $user = auth()->user();

        $owner_id = $group->user_id;

        if($user->id != $owner_id){
            return redirect()->route('groups.group.participants', $group->id)
            ->with('error', 'O usuário não é dono do grupo');
        }

        $group->delete();

        return redirect()->route('groups')->with('success', 'Grupo excluído com sucesso');
    }
    
    public function addParticipant(Request $request, Group $group){
        $request->validate([
            'email' => 'required|email|exists:users,email',
        ]);

        $user = auth()->user();

        $owner_id = $group->user_id;

        if($user->id != $owner_id){
            return redirect()->route('groups.group.participants', $group->id)
            ->with('error', 'O usuário não é dono do grupo');
        }

        $participant = User::where('email', $request->email)->first();

        $alreadyParticipant = $group->participants()->where('user_id', $participant->id)->exists();

        if ($alreadyParticipant) {
            return redirect()->route('groups.group.participants', $group->id)
                             ->with('error', 'O usuário já está como participante neste grupo');
        }

        $group->participants()->attach($participant->id, ['name' => $participant->name]);

        return redirect()->route('groups.group.participants', ['group' => $group->id])->with('success', 'Participante inserido com sucesso');

    }

    public function generateMatches(Group $group)
    {
        $user = auth()->user();

        $owner_id = $group->user_id;

        if($user->id != $owner_id){
            return redirect()->route('groups.group.participants', $group->id)
            ->with('error', 'O usuário não é dono do grupo');
        }

        $participants = $group->participants;

        if($participants->count() < 2 ){
            return redirect()->route('groups.group.participants', ['group' => $group->id])->with('error', 'Número inválido de participantes: mínimo 2.');
        }

        $participants = $participants->shuffle();

        $matches = $participants->zip($participants->skip(1)->push($participants->first()));

        foreach($matches as $match){
            $giver = $match[0];
            $receiver = $match[1];

            $group->participants()->updateExistingPivot($giver->id, [
                'name' => $receiver->name,
                'match_id' => $receiver->id,
            ]);
        }

    }

    public function removeParticipant(Group $group, User $user){

        if (!$group->participants->contains($user->id)) {
            return redirect()->route('groups.group.participants', ['group' => $group->id])
                             ->with('error', 'O usuário não é participante neste grupo');
        }

        $group->participants()->detach($user);

        return redirect()->route('groups.group.participants', ['group' => $group->id])->with('success', 'Participante removido com sucesso do grupo');
    }

    public function getMatch(Group $group, User $user)
    {
        $userP = Participant::where('user_id', $user->id)
                ->where('group_id', $group->id)
                ->first();

        if(!$userP){
            return redirect()->route('groups.group.participants', ['group' => $group->id])
            ->with('error', 'O usuário não é participante neste grupo');
        }

        $match = $userP->match_id;

        return view('match', compact('match'));
        //return response()->json(['match' => $match]);
    }

}
