<?php

namespace App\Http\Controllers\Secret;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Group;
use App\Models\User;

class SecretController extends Controller
{

    public function getGroups(){
        $groups = Group::all();
        $title = 'Grupos';

        return view('', compact('groups', 'title'));

    }

    public function createGroup(){
        $title = 'Criar Grupo';
        return view('', compact('title'));

    }
   
    public function storeGroup(Request $request){
        $group = Group::create([
            'name' => $request->name,
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

        $group->update([
            'name' => $request->name,
        ]);

        return redirect()->route('groups')->with('success', 'Grupo editado com sucesso');
    }

    public function destroyGroup(Request $request, Group $group){
        $group->delete();

        return redirect()->route('groups')->with('success', 'Grupo excluído com sucesso');
    }
    
    public function addParticipant(Request $request, Group $group){
        $request->validate([
            'email' => 'required|email|exists:users,email',
        ]);

        $user = User::where('email', $request->email)->first();

        if ($group->participants->contains($user)) {
            return redirect()->route('groups.group.participants', $group->id)
                             ->with('error', 'O usuário já está como participante neste grupo');
        }

        $group->participants()->attach($user->id, ['name' => $user->name]);

        return redirect()->route('groups.group.participants', ['group' => $group->id])->with('success', 'Participante inserido com sucesso');

    }

    public function generateMatches(Group $group)
    {
        // $user = auth()->user();
        
        // $users = $group->participants()->join('users as u1', 'u1.id', '=', 'participants.user_id')->select('u1.id')->pluck('u1.id')->shuffle();

        // if ($users->count() < 2) {
        //     //return response()->json(['error' => 'número inválido de participante: mínimo 2'], 400);
        // }

        // $matches = $users->zip($users->skip(1)->push($users->first()));

        // $matches->each(function ($pair) use ($group) {
        //     $giver = $pair[0];
        //     $receiver = $pair[1];
        
        //     $group->participants()->updateExistingPivot($giver, ['match_id' => $receiver]);
        // });

        // $participant = $group->participants()->where('user_id', $user->id)->first();

        // if ($participant && $participant->match_id) {
        //     // Se o usuário tem um match_id, redireciona para a página onde ele pode ver o sorteio
        //     return redirect()->route('groups.group.participant.getMatch', [
        //         'group' => $group->id, 
        //         'user' => $participant->match_id
        //     ])->with('success', 'Sorteio realizado com sucesso!');
        // }
    
        // // Caso contrário, redireciona para a página de participantes com uma mensagem de erro
        // return redirect()->route('groups.group.participants', ['group' => $group->id])
        //                  ->with('error', 'Erro ao gerar os matches. Tente novamente.');
        //return response()->json(['message' => 'Matches gerados com sucesso!']);
    }

    public function removeParticipant(Group $group, User $user){

        if (!$group->participants->contains($user->id)) {
            return redirect()->route('groups.group.participants', ['group' => $group->id])
                             ->with('error', 'O usuário não é participante neste grupo');
        }

        $group->participants()->detach($user);

        return redirect()->route('groups.group.participants', ['group' => $group->id])->with('success', 'Participante removido com sucesso do grupo');
    }

    public function getMatch(Group $group, Participant $participant)
    {
        $match = Participant::find($participant->match_id);

        //return response()->json(['match' => $match]);
    }

}
