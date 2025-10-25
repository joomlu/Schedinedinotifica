<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\TypeStreet;

class TypeStreetController extends Controller
{
    public function index()
    {
        $typestreet=TypeStreet::All();
        return view('typestreet.list',['typestreet' => $typestreet]);
    }

    public function store(Request $request)
    {
        $typestreet = new TypeStreet;
      $typestreet->create([             
                    'name' => $request->name,   
                    ]);
                  
                return redirect()->back();
    }

    public function datos()
    {
        
      
        return view('strutture.index');
        
    }


    public function update(Request $request)
        {
  
                $typestreet = TypeStreet::find($request->id);
                $typestreet->name = $request->name;
                $typestreet->save();
          
                    
            return redirect()->back();
        }

        public function destroy($id)
     { 
         $typestreet = TypeStreet::find($id);
         $typestreet->delete();
             return redirect()->back(); 
 
     }
}
