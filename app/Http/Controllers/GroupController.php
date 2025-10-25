<?php

namespace App\Http\Controllers;

use App\Models\Group;
use Illuminate\Http\Request;

class GroupController extends Controller
{
    public function index()
    {
        $groups = Group::All();

        return view('group.list', ['groups' => $groups]);
    }

    public function store(Request $request)
    {
        $grupo = new Group;
        $grupo->create([
            'name' => $request->name,
        ]);

        return redirect()->back();
    }

    public function update(Request $request)
    {

        $grupo = Group::find($request->id);
        $grupo->name = $request->name;
        $grupo->save();

        return redirect()->back();
    }

    public function destroy($id)
    {
        $grupo = Group::find($id);
        $grupo->delete();

        return redirect()->back();

    }
}
