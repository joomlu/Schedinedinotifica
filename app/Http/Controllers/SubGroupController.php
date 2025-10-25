<?php

namespace App\Http\Controllers;

use App\Models\SubGroup;
use Illuminate\Http\Request;

class SubGroupController extends Controller
{
    public function index()
    {
        $groups = SubGroup::All();

        return view('subgroup.list', ['groups' => $groups]);
    }

    public function store(Request $request)
    {
        $grupo = new SubGroup;
        $grupo->create([
            'name' => $request->name,
        ]);

        return redirect()->back();
    }

    public function update(Request $request)
    {

        $grupo = SubGroup::find($request->id);
        $grupo->name = $request->name;
        $grupo->save();

        return redirect()->back();
    }

    public function destroy($id)
    {
        $grupo = SubGroup::find($id);
        $grupo->delete();

        return redirect()->back();

    }
}
