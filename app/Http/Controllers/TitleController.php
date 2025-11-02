<?php

namespace App\Http\Controllers;

use App\Models\Title;
use Illuminate\Http\Request;

class TitleController extends Controller
{
    public function index()
    {
        $title = Title::All();

        return view('title.list', ['title' => $title]);
    }

    public function store(Request $request)
    {
        $title = new Title;
        $title->create([
            'name' => $request->name,
        ]);

        return redirect()->back();
    }

    public function update(Request $request)
    {

        $title = Title::find($request->id);
        $title->name = $request->name;
        $title->save();

        return redirect()->back();
    }

    public function destroy($id)
    {
        $title = Title::find($id);
        $title->delete();

        return redirect()->back();

    }
}
