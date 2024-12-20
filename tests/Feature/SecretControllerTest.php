<?php

namespace Tests\Feature;

use App\Models\Group;
use App\Models\User;
use App\Models\Participant;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Route;
use Tests\TestCase;

class SecretControllerTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function it_can_create_a_group()
    {

        $user = User::factory()->create();

        $this->actingAs($user);
        $response = $this->post(route('groups.addGroups'), [
            'name' => 'Novo Grupo',
        ]);

        $response->assertRedirect(route('groups'));
        $this->assertDatabaseHas('groups', [
            'name' => 'Novo Grupo',
        ]);
    }

    /** @test */
    public function it_can_add_a_participant_to_a_group()
    {
        $group = Group::factory()->create();
        $user = User::factory()->create();

        $this->actingAs($user);


        // Adicionar o usuário ao grupo
        $response = $this->post(route('groups.addParticipant', ['group' => $group->id]), [
            'email' => $user->email,
        ]);

        // Verifique se o redirecionamento ocorreu corretamente
        $response->assertRedirect(route('groups.group.participants', ['group' => $group->id]));

        // Verifique se o usuário foi adicionado ao grupo
        $this->assertTrue($group->participants->contains($user));

        // Verifique se a mensagem de sucesso foi adicionada à sessão
        $response->assertSessionHas('success', 'Participante inserido com sucesso');
    }

    /** @test */
    public function it_cannot_add_an_existing_participant_to_a_group()
    {
        // Criar um grupo e um usuário
        $group = Group::factory()->create();
        $user = User::factory()->create();

        $this->actingAs($user);


        // Adicionar o usuário ao grupo
        $group->participants()->attach($user->id, ['name' => $user->name]);

        // Tentar adicionar o mesmo usuário novamente
        $response = $this->post(route('groups.addParticipant', ['group' => $group->id]), [
            'email' => $user->email,
        ]);

        // Verifique se a página redirecionou corretamente com a mensagem de erro
        $response->assertRedirect(route('groups.group.participants', ['group' => $group->id]));
        $response->assertSessionHas('error', 'O usuário já está como participante neste grupo');
    }
    /** @test */
    public function it_can_remove_a_participant_from_a_group()
    {
        $group = Group::factory()->create();
        $user = User::factory()->create();

        $this->actingAs($user);
        // Adiciona o usuário ao grupo
        $group->participants()->attach($user->id, ['name' => $user->name]);

        $response = $this->delete(route('groups.removeParticipant', ['group' => $group->id, 'user' => $user->id]));

        $response->assertRedirect(route('groups.group.participants', ['group' => $group->id]));
        $this->assertFalse($group->participants->contains($user->id));
    }

    /** @test */
// public function it_can_render_the_edit_group_page()
// {
//     $group = Group::factory()->create();
//     $user = User::factory()->create();

//     $this->actingAs($user);

//     $response = $this->get(route('groups.edit', $group->id));

//     $response->assertStatus(200); // Verifica se a página foi carregada com sucesso
//     $response->assertViewIs('painel.initialpage'); // Substitua pelo nome correto da view
//     $response->assertViewHas('group', $group); // Verifica se o grupo está sendo enviado para a view
// }

/** @test */
public function it_can_update_a_group()
{
    $user = User::factory()->create();

        $this->actingAs($user);

    $group = Group::factory()->create([
        'name' => 'Old Group Name',
    ]);

    $newData = [
        'name' => 'Updated Group Name',
    ];

    $response = $this->put(route('groups.update', $group->id), $newData);

    $response->assertRedirect(route('groups')); // Verifica redirecionamento
    $response->assertSessionHas('success', 'Grupo editado com sucesso'); // Verifica mensagem de sucesso

    $this->assertDatabaseHas('groups', [
        'id' => $group->id,
        'name' => $newData['name'], // Verifica se os dados foram atualizados no banco
    ]);
}

/** @test */
public function it_can_delete_a_group()
{

    $user = User::factory()->create();

        $this->actingAs($user);
    $group = Group::factory()->create();

    $response = $this->delete(route('groups.destroy', $group->id));

    $response->assertRedirect(route('groups')); // Verifica redirecionamento
    $response->assertSessionHas('success', 'Grupo excluído com sucesso'); // Verifica mensagem de sucesso

    $this->assertDatabaseMissing('groups', [
        'id' => $group->id, // Verifica se o grupo foi excluído do banco
    ]);
}

 /** @test */

 public function test_generateMatches_user_not_participant()
 {
     $user = User::factory()->create();
     $group = Group::factory()->create();

     $this->actingAs($user);
     // Ação como usuário que não é participante
     $response = $this->actingAs($user)->post(route('groups.generateMatches', $group->id));

     // Verifica se o usuário é redirecionado para a página de participantes com erro
     $response->assertRedirect(route('groups.group.participants', ['group' => $group->id]));
     $response->assertSessionHas('error', 'Usuário não é participante');
 }

 public function test_generateMatches_not_enough_participants()
 {
     $user = User::factory()->create();
     $group = Group::factory()->create();

     $this->actingAs($user);

     // Adiciona o usuário como participante
     $group->participants()->attach($user->id , ['name'=> $user->name]);


     // Ação como usuário que é participante, mas com menos de 2 participantes no grupo
     $response = $this->actingAs($user)->post(route('groups.generateMatches', $group->id));

     // Verifica se o redirecionamento ocorre com a mensagem de erro
     $response->assertRedirect(route('groups.group.participants', ['group' => $group->id]));
     $response->assertSessionHas('error', 'Número inválido de participantes: mínimo 2.');
 }

 public function test_generateMatches_success()
 {
     $user = User::factory()->create();
     $group = Group::factory()->create();

     $this->actingAs($user);

     // Adiciona dois participantes
     $group->participants()->create(['user_id' => $user->id, 'name'=> $user->name]);
     $participant2 = User::factory()->create();
     $group->participants()->create(['user_id' => $participant2->id, 'name'=> $participant2->name]);

     // Ação com usuário participante e mais de 1 participante no grupo
     $response = $this->actingAs($user)->post(route('groups.generateMatches', $group->id));

     // Verifica se o redirecionamento ocorre com sucesso
     $response->assertRedirect(route('groups.group.participant.getMatch', [
         'group' => $group->id,
         'user' => $user->id // Verifica que o match_id está sendo atribuído corretamente
     ]));
     $response->assertSessionHas('success', 'Sorteio realizado com sucesso!');
     
     // Verifica se o match_id foi atribuído
     $this->assertDatabaseHas('participants', [
         'user_id' => $user->id,
         'match_id' => $participant2->id, // Verifica se o match_id foi atribuído corretamente
     ]);
 }

 public function test_generateMatches_no_match_for_user()
 {
     $user = User::factory()->create();
     $group = Group::factory()->create();

     $this->actingAs($user);

     // Adiciona um único participante
     $group->participants()->create(['user_id' => $user->id, 'name' => $user->name]);

     // Ação com um único participante no grupo
     $response = $this->actingAs($user)->post(route('groups.generateMatches', $group->id));

     // Verifica se o redirecionamento ocorre com erro devido à falta de participantes
     $response->assertRedirect(route('groups.group.participants', ['group' => $group->id]));
     $response->assertSessionHas('error', 'Número inválido de participantes: mínimo 2.');
 }


}

