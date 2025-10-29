<?php

namespace App\Http\Controllers;

use App\Models\Comuni;
use App\Models\Schedina;
use App\Models\Title;
use App\Models\TypeDoc;
use App\Models\TypeStreet;
use App\Services\NationService;
use Carbon\Carbon;
use Illuminate\Http\Request;

class SchedinaController extends Controller
{
    protected $nationService;

    public function __construct(NationService $nationService)
    {
        $this->nationService = $nationService;
    }

    public function index()
    {
        $schedinas = Schedina::where('is_arrive', 0)->get();

        return view('schedina.list', ['schedinas' => $schedinas]);
    }

    public function new()
    {
        $titles = Title::All();
        $typestreets = TypeStreet::All();
        $TypeDocs = TypeDoc::All();
    $nations = $this->nationService->getAllNations();
        $regions = $this->nationService->getAllRegions();
        $provinces = $this->nationService->getAllProvinces();
        $ciudades = Comuni::All();

        return view('schedina.new', ['titles' => $titles, 'typestreets' => $typestreets, 'TypeDocs' => $TypeDocs, 'nations' => $nations,
            'regions' => $regions,
            'provinces' => $provinces,
            'ciudades' => $ciudades, ]);
    }

    public function store(Request $request)
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

        // Crear el nuevo registro con los datos recibidos y "scheda"
        $schedina = new Schedina;
        $schedina->create([
            'type' => $request->type,
            'name' => $request->name,
            'surname' => $request->surname,
            'customer_id' => $request->customer_id,
            'sex' => $request->sex,
            'relationship' => $request->relationship,
            'exent' => $request->exent,
            'arrive' => $request->arrive ? Carbon::parse($request->arrive)->format('d/m/Y') : null,
            'departure' => $request->departure ? Carbon::parse($request->departure)->format('d/m/Y') : null,
            'cant_people' => $request->cant_people,
            'room' => $request->room,
            'beds' => $request->beds,
            'observation' => $request->observation,
            'oa_country' => $request->oa_country,
            'oa_city' => $request->oa_city,
            'oa_region' => $request->oa_region,
            'oa_prov' => $request->oa_prov,
            'oa_city_nac' => $request->oa_city_nac,
            'oa_date_nac' => $request->oa_date_nac ? Carbon::parse($request->oa_date_nac)->format('d/m/Y') : null,
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
            'or_published_date' => $request->or_published_date ? Carbon::parse($request->or_published_date)->format('d/m/Y') : null,
            'or_expire' => $request->or_expire ? Carbon::parse($request->or_expire)->format('d/m/Y') : null,
            'or_published' => $request->or_published,
            'or_published_country' => $request->or_published_country,
            'scheda' => $scheda, // Guardar el valor generado de "scheda"
        ]);

        return redirect('/schedina');
    }

    public function edit($id)
    {
        $schedina = Schedina::find($id);
        $titles = Title::All();
        $typestreets = TypeStreet::All();
        $TypeDocs = TypeDoc::All();

        return view('schedina.edit', ['schedina' => $schedina, 'titles' => $titles, 'typestreets' => $typestreets, 'TypeDocs' => $TypeDocs]);
    }

    public function update(Request $request)
    {

        $schedina = Schedina::find($request->id);
        $schedina->group = $request->group;
        $schedina->subgroup = $request->subgroup;
        $schedina->subgroup1 = $request->subgroup1;
        $schedina->sex = $request->sex;
        $schedina->type_housed = $request->type_housed;
        $schedina->type = $request->type;
        $schedina->name = $request->name;
        $schedina->surname = $request->surname;
        $schedina->country = $request->country;
        $schedina->city = $request->city;
        $schedina->region = $request->region;
        $schedina->save();

        return redirect()->back();
    }

    public function destroy($id)
    {
        $schedina = Schedina::find($id);
        $schedina->delete();

        return redirect()->back();

    }
}
