<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class LevelController extends Controller
{
    public function index()
    {
        $levels = \App\Models\Level::all();
        return view("admin.roles.view", compact('levels'));
    }

    public function create()
    {
        return view("admin.roles.add");
    }
    public function store(Request $request)
    {
        $request->validate([
          'name' => 'required|unique:levels',
      'rank' => 'required',
      'status_label' => 'required',
        ]);
    }
}
