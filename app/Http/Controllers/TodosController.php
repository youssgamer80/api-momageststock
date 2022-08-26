<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Todos;
use App\Http\Controllers\Controller;
use App\Http\Resources\TodosRessource;


class TodosController extends Controller
{
    public function __construct(){

        $this->middleware('auth.role:admin');
    }
    

    public function index(){

        $todos = Todos::All();

        return sendResponse(New Ressource($todos),"liste de toutes les taches");
    }

    public function create(Request $request){

        $request->validate([

            'title' => 'required|String|max:55',
            'description' => 'required|String|max:255',
        ]);
        
        $todo = Todos::create([

            'title'=> $request->title,
            'description'=>$request->description,
        ]);

        return response()->json([

            'status' => 'todo create successfully',
            'data' => $todo,
        ]);
    }

    public function show(Request $id){

        $todo = Todos::find($id);

        return sendResponse(New TodosRessource($todo), "tache rouvée avec success");
    }
}
