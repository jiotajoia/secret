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

// Route::get('/dashboard', function () {
//     return view('dashboard');
// })->middleware(['auth', 'verified'])->name('dashboard');

Route::middleware('auth')->group(function () {

    //initial painel
    Route::get('/painel/initialpage', [PainelController::class, 'index'])->name('paginaInicial');//initial page

    //secret
    Route::post('/groups', [SecretController::class, 'createGroup']); //create group
    Route::post('/groups/{group}/participants', [SecretController::class, 'addParticipants']);
    Route::post('/groups/{group}/generate-matches', [SecretController::class, 'generateMatches']);
    Route::get('/groups/{group}/present/{participant}', [SecretController::class, 'getMatch']);
    
        //default for auth
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

require __DIR__.'/auth.php';
