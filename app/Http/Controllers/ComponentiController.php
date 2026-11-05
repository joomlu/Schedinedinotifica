<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Componenti;
use App\Models\Gruppo;
// Gruppi secondari rimossi: tabelle eliminate
use App\Models\TipoVia;
use App\Services\CestinoService;
use App\Services\NationService;
use App\Models\GeoNazione;
use App\Models\GeoComune;
use Illuminate\Support\Facades\Schema;

class ComponentiController extends Controller
{
    protected $nationService;

    public function __construct(NationService $nationService)
    {
        $this->nationService = $nationService;
    }
    
    public function index()
    {
        $componenti=Componenti::All();
        return view('componenti.list',['componenti' => $componenti]);
    }

    public function new(Request $request)
    {
        $schedina_id=$request->schedina_id;
        $customer_id=$request->customer_id;
        $typeaway = TipoVia::query()->orderBy('nome')->get(['id', 'nome as name']);
        $nations     = GeoNazione::orderBy('nome')->get(['id', 'nome', 'cittadinanza', 'codice_iso2']);
        $regions     = $this->nationService->getAllRegions();
        $provinces     = $this->nationService->getAllProvinces();
        $ciudades     = GeoComune::orderBy('nome')->get(['id', 'nome']);
        return view('componenti.new',
        ['schedina_id' => $schedina_id, 
        'customer_id' => $customer_id, 
        'typeaway' => $typeaway,
        'nations'     => $nations,
            'regions'     => $regions,
            'provinces'     => $provinces,
            'ciudades'     => $ciudades,
    ]);
    }

    public function store(Request $request)
    {
        $componenti = new Componenti;
      $componenti->create([             
                    'schedina_id'=> $request->schedina_id, 
                    'customer_id'=> $request->customer_id, 
                    'sex' => $request->sex,   
                    'relationship' => $request->relationship,
                    'exent' => $request->exent,
                    'name' => $request->name,
                    'surname' => $request->surname,
                    'country' => $request->country,
                    'city' => $request->city,   
                    'regione' => $request->regione,
                    'province_nac' => $request->province,
                    'cap' => $request->cap,
                    'tipeaway' => $request->tipeaway,
                    'address' => $request->address,
                    'number' => $request->number,   
                    'city_nac' => $request->city_nac,
                    'date_nac' => $request->date_nac,
                    'nac_reg' => $request->nac_reg,
                    ]);
                  
                return redirect('/schedina');
    }

    public function edit($id)
    {
        $componenti=Componenti::find($id);
        $groups = Schema::hasTable('gruppi') ? Gruppo::all() : collect();
        $nations     = GeoNazione::orderBy('nome')->get(['id', 'nome', 'cittadinanza', 'codice_iso2']);
        $regions     = $this->nationService->getAllRegions();
        $provinces     = $this->nationService->getAllProvinces();
        $ciudades     = GeoComune::orderBy('nome')->get(['id', 'nome']);

        return view('componenti.edit',[ 
            'componenti' => $componenti,
            'groups' => $groups,
            'nations'     => $nations,
            'regions'     => $regions,
            'provinces'     => $provinces,
            'ciudades'     => $ciudades,
        ]);
    }

    public function update(Request $request)
        {
  
                $customer = Componenti::find($request->id);
                $customer->schedina_id = $request->schedina_id;
                $customer->customer_id = $request->customer_id;
                $customer->relationship = $request->relationship;
                $customer->sex = $request->sex;
                $customer->exent = $request->exent;
                $customer->name = $request->name;
                $customer->surname = $request->surname;
                $customer->country = $request->country;
                $customer->city = $request->city;
                $customer->regione = $request->regione;
                $customer->province_nac = $request->province_nac;
                $customer->cap = $request->cap;
                $customer->typeaway = $request->tipeaway;
                $customer->address = $request->address;
                $customer->number = $request->number;
                
                $customer->city_nac = $request->city_nac;
                $customer->date_nac = $request->date_nac;
                
                
                
                $customer->save();
          
                    
            return redirect()->back();
        }



    public function destroy($id)
    { 
        $customer = Componenti::find($id);
        app(CestinoService::class)->archiveModel($customer, [
            'source' => 'Componenti',
        ]);
        $customer->delete();
            return redirect()->back()->with('success', 'Componente spostato nel cestino.'); 

    }

}
