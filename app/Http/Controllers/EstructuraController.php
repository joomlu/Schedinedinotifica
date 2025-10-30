<?php

namespace App\Http\Controllers;

use App\Models\Estructura;
use App\Models\Tassa;
use Illuminate\Http\Request;
use Illuminate\Contracts\View\View as ViewContract;
use Illuminate\View\View;
use Illuminate\Http\RedirectResponse;

class EstructuraController extends Controller
{
    public function __construct()
    {
        // Superadmin, admin y cliente pueden crear/modificar estructuras
        $this->middleware('permission:create structures')->only(['store', 'update']);
    }

    public function index(): View|ViewContract
    {
        $estructura = Estructura::where('id', '>', 0)->first();
        $tasa = Tassa::where('id', '>', 0)->first();
        if (empty($tasa)) {
            $tasa = new Tassa;
            $tasa->create([
                'tassa_soggiorno' => '4.5',
            ]);
        }

        if (empty($estructura)) {
            $estructura = new Estructura;
            $estructura->create([
                'name' => 'Novo Nome',
            ]);
            $tasa = Tassa::where('id', '>', 0)->first();

            return view('estructura.index', ['estructura' => $estructura, 'tasa' => $tasa]);
        } else {
            $tasa = Tassa::where('id', '>', 0)->first();

            return view('estructura.index', ['estructura' => $estructura, 'tasa' => $tasa]);
        }

    }

    public function update(Request $request): RedirectResponse
    {

        $estructura = Estructura::find($request->id);
        $estructura->name = $request->name;
        $estructura->phone = $request->phone;
        $estructura->city = $request->city;

        if ($request->hasFile('logo')) {
            $logo = $request->file('logo');
            $filename = time().'_'.$logo->getClientOriginalName();
            $path = $logo->storeAs('public/logos', $filename);
            $estructura->logo = $filename;
        }
        $estructura->fax = $request->fax;
        $estructura->address = $request->address;
        $estructura->email = $request->email;
        $estructura->cp = $request->cp;
        $estructura->web = $request->web;
        $estructura->cf = $request->cf;
        $estructura->piva = $request->piva;
        $estructura->startact = $request->startact;
        $estructura->typology = $request->typology;
        $estructura->closeact = $request->closeact;
        $estructura->clasification = $request->clasification;
        $estructura->numshedine = $request->numshedine;
        $estructura->roomdisp = $request->roomdisp;
        $estructura->ref = $request->ref;
        $estructura->beddisp = $request->beddisp;
        $estructura->refpass = $request->refpass;
        $estructura->updatedbed = $request->updatedbed;
        $estructura->save();

        return redirect()->back();
    }

    public function tasaupdate(Request $request): RedirectResponse
    {

        $tasa = Tassa::find($request->id);
        $tasa->tassa_soggiorno = $request->tassa_soggiorno;
        $tasa->giorni_massimo = $request->giorni_massimo;
        $tasa->inizio = $request->inizio;
        $tasa->fine = $request->fine;
        $tasa->province = $request->province;
        $tasa->city = $request->city;
        $tasa->region = $request->region;
        $tasa->max_age_children = $request->max_age_children;
        $tasa->min_age_adult = $request->min_age_adult;

        if ($request->hasFile('logo')) {
            $logo = $request->file('logo');
            $filename = time().'_'.$logo->getClientOriginalName();
            $path = $logo->storeAs('public/logos', $filename);
            $tasa->logo = $filename;
        }
        $tasa->save();

        // keep the Tasa Soggiorno tab active after saving
        return redirect()->back()->with('active_tab', 'product1');
    }
}
