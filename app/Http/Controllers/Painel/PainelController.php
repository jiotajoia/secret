<?php

namespace App\Http\Controllers\Painel;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class PainelController extends Controller
{
    public function index(){
        $title = "Página Inicial";
        return view('painel.initialpage', compact('title'));
    }
    

}
