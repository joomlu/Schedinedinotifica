<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\TypeDoc;

class TypeDocController extends Controller
{
    public function index()
    {
        $typedoc=TypeDoc::All();
        return view('typedoc.list',['typedoc' => $typedoc]);
    }

    public function store(Request $request)
    {
        $typedoc = new TypeDoc;
      $typedoc->create([             
                    'name' => $request->name,   
                    ]);
                  
                return redirect()->back();
    }

    public function update(Request $request)
        {
  
                $typedoc = TypeDoc::find($request->id);
                $typedoc->name = $request->name;
                $typedoc->save();
          
                    
            return redirect()->back();
        }

        public function destroy($id)
     { 
         $typedoc = TypeDoc::find($id);
         $typedoc->delete();
             return redirect()->back(); 
 
     }
}
