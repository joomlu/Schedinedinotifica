<?php

namespace App\Http\Controllers;

use App\Models\Customers;
use App\Models\Group;
use App\Models\SubGroup;
use App\Models\SubGroup1;
use App\Models\TypeDoc;
use App\Models\TypeStreet;
use App\Services\NationService;
use Illuminate\Http\Request;
use Illuminate\Contracts\View\View as ViewContract;
use Illuminate\View\View;
use Illuminate\Http\RedirectResponse;

class CustomerController extends Controller
{
    protected NationService $nationService;

    public function __construct(NationService $nationService)
    {
        $this->nationService = $nationService;
        // Superadmin, admin y cliente pueden crear clientes
        $this->middleware('permission:create clients')->only(['store']);
    }

    public function index(): View|ViewContract
    {
        $user = auth()->user();

        // Superadmin ve tutti i customers
        if ($user->isSuperAdmin()) {
            $customers = Customers::all();
        }
        // Admin, Hotel Owner e Staff vedono solo i customers della loro struttura
        elseif ($user->structure_id) {
            $customers = Customers::where('structure_id', $user->structure_id)->get();
        }
        // Fallback: nessun customer
        else {
            $customers = collect();
        }

        return view('customers.list', ['customers' => $customers]);
    }

    public function new(): View|ViewContract
    {
        $groups = Group::all();
        $subgroups = SubGroup::all();
        $subgroups1 = SubGroup1::all();
        $typestreets = TypeStreet::All();
        $TypeDocs = TypeDoc::All();
        $nations = $this->nationService->getAllNations();
        $regions = $this->nationService->getAllRegions();
        $provinces = $this->nationService->getAllProvinces();
        $ciudades = $this->nationService->getAllCities();

        // dd($nations);
        return view('customers.new', [
            'groups' => $groups,
            'subgroups' => $subgroups,
            'subgroups1' => $subgroups1,
            'typestreets' => $typestreets,
            'TypeDocs' => $TypeDocs,
            'nations' => $nations,
            'regions' => $regions,
            'provinces' => $provinces,
            'ciudades' => $ciudades,
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $customers = new Customers;
        $customers->create([
            'group' => $request->group,
            'subgroup' => $request->subgroup,
            'subgroup1' => $request->subgroup1,
            'sex' => $request->sex,
            'type_housed' => $request->type_housed,
            'type' => $request->type,
            'name' => $request->name,
            'surname' => $request->surname,
            'country' => $request->country,
            'city' => $request->city,
            'region' => $request->region,
            'province' => $request->province,
            'cap' => $request->cap,
            'typeaway' => $request->typeaway,
            'address' => $request->address,
            'number' => $request->number,
            'email' => $request->email,
            'phone' => $request->phone,
            'fax' => $request->fax,
            'cellphone' => $request->cellphone,
            'observation' => $request->observation,
            'country_reg' => $request->country_reg,
            'city_reg' => $request->city_reg,
            'prov_reg' => $request->prov_reg,
            'ciudadania_reg' => $request->ciudadania_reg,
            'nac_reg' => $request->nac_reg,
            'type_doc_reg' => $request->type_doc_reg,
            'num_doc_reg' => $request->num_doc_reg,
            'date_pub_reg' => $request->date_pub_reg,
            'expire_reg' => $request->expire_reg,
            'released_reg' => $request->released_reg,
            'observation_reg' => $request->observation_reg,
            'azienda' => $request->azienda,
            'cap_az' => $request->cap_az,
            'cf_az' => $request->cf_az,
            'pi_az' => $request->pi_az,
            'typeaway_az' => $request->typeaway_az,
            'address_az' => $request->address_az,
            'number_az' => $request->number_az,
            'email_az' => $request->email_az,
            'phone_az' => $request->phone_az,
            'fax_az' => $request->fax_az,
            'cellphone_az' => $request->cellphone_az,
            'country_az' => $request->country_az,
            'city_az' => $request->city_az,
            'region_az' => $request->region_az,
            'province_az' => $request->province_az,
            'desc_az' => $request->desc_az,
            // collega il customer alla struttura dell'utente (se presente)
            'structure_id' => auth()->user()->structure_id,
        ]);

        return redirect('/customers');
    }

    public function edit(int $id): View|ViewContract
    {
        $customer = Customers::find($id);
        $groups = Group::all();
        $subgroups = SubGroup::all();
        $subgroups1 = SubGroup1::all();

        return view('customers.edit', ['customer' => $customer, 'groups' => $groups, 'subgroups' => $subgroups, 'subgroups1' => $subgroups1]);
    }

    public function update(Request $request): RedirectResponse
    {

        $customer = Customers::find($request->id);
        $customer->group = $request->group;
        $customer->subgroup = $request->subgroup;
        $customer->subgroup1 = $request->subgroup1;
        $customer->sex = $request->sex;
        $customer->type_housed = $request->type_housed;
        $customer->type = $request->type;
        $customer->name = $request->name;
        $customer->surname = $request->surname;
        $customer->country = $request->country;
        $customer->city = $request->city;
        $customer->region = $request->region;
        $customer->province = $request->province;
        $customer->cap = $request->cap;
        $customer->typeaway = $request->typeaway;
        $customer->address = $request->address;
        $customer->number = $request->number;
        $customer->email = $request->email;
        $customer->phone = $request->phone;
        $customer->fax = $request->fax;
        $customer->observation = $request->observation;
        $customer->country_reg = $request->surcountry_regname;
        $customer->city_reg = $request->city_reg;
        $customer->prov_reg = $request->prov_reg;
        $customer->ciudadania_reg = $request->ciudadania_reg;
        $customer->nac_reg = $request->nac_reg ? \Carbon\Carbon::parse($request->nac_reg)->format('d/m/Y') : null;
        $customer->type_doc_reg = $request->type_doc_reg;
        $customer->num_doc_reg = $request->num_doc_reg;
        $customer->date_pub_reg = $request->date_pub_reg;
        $customer->expire_reg = $request->expire_reg ? \Carbon\Carbon::parse($request->expire_reg)->format('d/m/Y') : null;
        $customer->released_reg = $request->released_reg ? \Carbon\Carbon::parse($request->released_reg)->format('d/m/Y') : null;
        $customer->observation_reg = $request->observation_reg;
        $customer->azienda = $request->azienda;
        $customer->cap_az = $request->cap_az;
        $customer->cf_az = $request->cf_az;
        $customer->pi_az = $request->pi_az;
        $customer->typeaway_az = $request->typeaway_az;
        $customer->address_az = $request->address_az;
        $customer->number_az = $request->number_az;
        $customer->email_az = $request->email_az;
        $customer->phone_az = $request->phone_az;
        $customer->fax_az = $request->fax_az;
        $customer->cellphone_az = $request->cellphone_az;
        $customer->country_az = $request->country_az;
        $customer->city_az = $request->city_az;
        $customer->region_az = $request->region_az;
        $customer->province_az = $request->province_az;
        $customer->desc_az = $request->desc_az;
        $customer->save();

        return redirect()->back();
    }

    public function destroy(int $id): RedirectResponse
    {
        $customer = Customers::find($id);
        $customer->delete();

        return redirect()->back();

    }
}
