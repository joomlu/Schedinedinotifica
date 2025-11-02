<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Released;

class ReleasedController extends Controller
{
    public function index()
    {
        $released=Released::All();
        return view('released.list',['released' => $released]);
    }

    public function store(Request $request)
    {
        $released = new Released;
      $released->create([             
                    'name' => $request->name,   
                    ]);
                  
                return redirect()->back();
    }

    public function update(Request $request)
        {
  
                $released = Released::find($request->id);
                $released->name = $request->name;
                $released->save();
          
                    
            return redirect()->back();
        }

        public function destroy($id)
     { 
         $released = Released::find($id);
         $released->delete();
             return redirect()->back(); 
 
     }
}
