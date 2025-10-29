<?php

namespace App\Http\Controllers;

use App\Models\Componenti;
use App\Models\Group;
use App\Models\SubGroup;
use App\Models\SubGroup1;
use App\Models\TypeStreet;
use App\Services\NationService;
use Illuminate\Http\Request;

class ComponentiController extends Controller
{
    protected $nationService;

    public function __construct(NationService $nationService)
    {
        $this->nationService = $nationService;
    }

    public function index()
    {
        $componenti = Componenti::All();

        return view('componenti.list', ['componenti' => $componenti]);
    }

    public function new(Request $request)
    {
        $schedina_id = $request->schedina_id;
        $customer_id = $request->customer_id;
        $typeaway = TypeStreet::All();
        $nations = $this->nationService->getAllNations();
        $regions = $this->nationService->getAllRegions();
        $provinces = $this->nationService->getAllProvinces();
        $ciudades = $this->nationService->getAllCities();

        return view('componenti.new',
            ['schedina_id' => $schedina_id,
                'customer_id' => $customer_id,
                'typeaway' => $typeaway,
                'nations' => $nations,
                'regions' => $regions,
                'provinces' => $provinces,
                'ciudades' => $ciudades,
            ]);
    }

    public function store(Request $request)
    {
        $componenti = new Componenti;
        $componenti->create([
            'schedina_id' => $request->schedina_id,
            'customer_id' => $request->customer_id,
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
            // Align typo from request to column name
            'typeaway' => $request->tipeaway,
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
        $componenti = Componenti::find($id);
        $groups = Group::all();
        $subgroups = SubGroup::all();
        $subgroups1 = SubGroup1::all();
        $typeaway = TypeStreet::All();
        $nations = $this->nationService->getAllNations();
        $regions = $this->nationService->getAllRegions();
        $provinces = $this->nationService->getAllProvinces();
        $ciudades = $this->nationService->getAllCities();

        return view('componenti.edit',
            ['componenti' => $componenti,
                'typeaway' => $typeaway,
                'groups' => $groups,
                'subgroups' => $subgroups,
                'subgroups1' => $subgroups1,
                'nations' => $nations,
                'regions' => $regions,
                'provinces' => $provinces,
                'ciudades' => $ciudades,
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
        $customer->delete();

        return redirect()->back();

    }
}
