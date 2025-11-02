<?php

namespace App\Http\Controllers;

use App\Models\Customers;
use App\Models\Schedina;
use App\Models\Title;
use App\Models\TypeDoc;
use App\Models\TypeStreet;
use App\Services\NationService;
use Illuminate\Http\Request;

class ArrivalsController extends Controller
{
    protected $nationService;

    public function __construct(NationService $nationService)
    {
        $this->nationService = $nationService;
    }

    public function index()
    {
        $arrivals = Schedina::where('is_arrive', 1)->get();

        return view('arrivals.list', ['arrivals' => $arrivals]);
    }

    public function new()
    {
        $titles = Title::All();
        $typestreets = TypeStreet::All();
        $TypeDocs = TypeDoc::All();
        $nations = $this->nationService->getAllNations();
        $regions = $this->nationService->getAllRegions();
        $provinces = $this->nationService->getAllProvinces();
        $ciudades = $this->nationService->getAllCities();

        return view('arrivals.new',
            ['titles' => $titles,
                'typestreets' => $typestreets,
                'TypeDocs' => $TypeDocs,
                'nations' => $nations,
                'regions' => $regions,
                'provinces' => $provinces,
                'ciudades' => $ciudades,
            ]);
    }

    public function search(Request $request)
    {
        $query = $request->input('query');

        // Realiza la búsqueda en la base de datos
        $results = Customers::where('name', 'LIKE', "%{$query}%") // Cambia "name" por el campo que estás buscando
            ->orWhere('email', 'LIKE', "%{$query}%") // Ejemplo con otro campo
            ->take(10) // Limitar resultados
            ->get();

        //dd($results);
        // Devuelve los resultados como JSON
        return response()->json($results);
    }

    public function store(Request $request)
    {
        $schedina = new Schedina;
        $schedina->create([
            'type' => $request->type,
            'name' => $request->name,
            'surname' => $request->surname,
            'customer_id' => $request->customer_id,
            'sex' => $request->sex,
            'relationship' => $request->relationship,
            'exent' => $request->exent,
            'arrive' => $request->arrive,
            'departure' => $request->departure,
            'cant_people' => $request->cant_people,
            'room' => $request->room,
            'beds' => $request->beds,
            'observation' => $request->observation,
            'oa_country' => $request->oa_country,
            'oa_city' => $request->oa_city,
            'oa_region' => $request->oa_region,
            'oa_prov' => $request->oa_prov,
            'oa_city_nac' => $request->oa_city_nac,
            'oa_date_nac' => $request->oa_date_nac,
            'or_country' => $request->or_country,
            'or_city' => $request->or_city,
            'or_region' => $request->or_region,
            'or_prov' => $request->or_prov,
            'or_cap' => $request->or_cap,
            'or_typeaway' => $request->or_typeaway,
            'or_address' => $request->or_address,
            'or_num' => $request->or_num,
            'or_doc' => $request->or_doc,
            'or_doctype' => $request->or_doctype,
            'or_published_date' => $request->or_published_date,
            'or_expire' => $request->or_expire,
            'or_published' => $request->or_published,
            'or_published_country' => $request->or_published_country,
            'is_arrive' => 1,
        ]);

        return redirect('/arrivals');
    }

    public function update(Request $request)
    {
        $schedina = Schedina::find($request->id);
        $schedina->name = $request->name;
        $schedina->save();

        return redirect()->back();

    }

    public function a_schedina($id)
    {
        // Obtener el último número consecutivo de "scheda"
        $lastSchedina = Schedina::orderBy('id', 'desc')->first();

        // Extraer el número consecutivo de 3 cifras, si existe, o empezar desde 1
        $lastNumber = 0;
        if ($lastSchedina && preg_match('/^(\d+)-\d{4}$/', $lastSchedina->scheda, $matches)) {
            $lastNumber = (int) $matches[1];
        }
        $newNumber = str_pad($lastNumber + 1, 3, '0', STR_PAD_LEFT);

        // Obtener el año corriente
        $currentYear = date('Y');

        // Generar el valor de "scheda"
        $scheda = "{$newNumber}-{$currentYear}";
        // Encuentra la entrada por ID
        $schedina = Schedina::find($id);

        // Actualiza el campo is_arrive a 0
        if ($schedina) {
            $schedina->is_arrive = 0;
            $schedina->scheda = $scheda;
            $schedina->save();
        }

        // Redirige de vuelta a la lista de arrivals
        return redirect()->back()->with('success', 'El registro fue actualizado a "no arrived".');
    }

    public function destroy($id)
    {
        $schedina = Schedina::find($id);
        $schedina->delete();

        return redirect()->back();
    }
}
