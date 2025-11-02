<?php

namespace App\Http\Controllers;

use App\Models\SubGroup1;
use Illuminate\Http\Request;

class SubGroup1Controller extends Controller
{
    public function index()
    {
        $groups = SubGroup1::All();

        return view('subgroup1.list', ['groups' => $groups]);
    }

    public function store(Request $request)
    {
        $grupo = new SubGroup1;
        $grupo->create([
            'name' => $request->name,
        ]);

        return redirect()->back();
    }

    public function update(Request $request)
    {

        $grupo = SubGroup1::find($request->id);
        $grupo->name = $request->name;
        $grupo->save();

        return redirect()->back();
    }

    public function destroy($id)
    {
        $grupo = SubGroup1::find($id);
        $grupo->delete();

        return redirect()->back();

    }
}
