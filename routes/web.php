<?php

use App\Http\Controllers\ProfileController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Painel\PainelController;
use App\Http\Controllers\Secret\SecretController;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "web" middleware group. Make something great!
|
*/

Route::get('/', function () {
    return view('welcome');
});

Route::middleware('auth')->group(function () {

    //initial painel
    Route::get('/painel/initialpage', [PainelController::class, 'index'])->name('initialPage');//initial page

    //secret
        //groups
    Route::get('/groups', [SecretController::class, 'getGroups'])->name('groups');//get groups
    Route::get('/groups/create', [SecretController::class, 'createGroup'])->name('groups.create'); //create group form
    Route::post('/groups', [SecretController::class, 'storeGroup'])->name('groups.addGroups'); //create group effective
    Route::get('/groups/{group}',[SecretController::class, 'showGroup'])->name('groups.show');  //show group
    Route::get('groups/{group}/edit', [SecretController::class, 'editGroup'])->name('groups.edit'); //edit groups
    Route::put('/groups/{group}', [SecretController::class, 'updateGroup'])->name('groups.update'); //update groups
    Route::delete('/groups/{group}', [SecretController::class, 'destroyGroup'])->name('groups.destroy'); //remove groups

         //groups -> participants
    Route::get('/groups/{group}/participants', [SecretController::class, 'showGroup'])->name('groups.group.participants'); //get participants from a group
    Route::post('/groups/{group}/participants/addParticipant', [SecretController::class, 'addParticipant'])->name('groups.addParticipant'); //add participant to a group
    Route::delete('/groups/{group}/participants/{user}', [SecretController::class, 'removeParticipant'])->name('groups.removeParticipant'); //remove participant from group
    Route::post('/groups/{group}/generateMatches', [SecretController::class, 'generateMatches'])->name('groups.generateMatches'); //generate matches
    Route::get('/groups/{group}/present/{user}', [SecretController::class, 'getMatch'])->name('groups.group.participant.getMatch'); //show match
    
    //Route::resource('groups', SecretController::class);
        //default for auth
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

require __DIR__.'/auth.php';
