<?php

namespace App\Http\Controllers;

use App\Models\Customers;
use App\Services\CestinoService;
use App\Support\StrutturaCorrente;
use Illuminate\Support\Facades\DB;
use App\Models\GeoComune;
use App\Models\GeoNazione;
use App\Models\TipoCliente;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;

class CustomerExportController extends Controller
{
    public function __construct()
    {
        $this->guardArea();
    }

    private function guardArea(): void
    {
        $this->middleware(\App\Http\Middleware\RequireAuthorizedStruttura::class);
        $this->middleware(function (Request $request, $next) {
            $struttura = \App\Support\StrutturaAccess::authorized($request);
            abort_unless(\App\Support\StrutturaAccess::query($request->user())->whereKey($struttura->id)->exists(), 403);
            return $next($request);
        });
    }

    public function index(Request $request)
    {
        $query = $this->buildFilteredQuery($request);
        session()->put('clienti_origine.'.StrutturaCorrente::getId(), 'export');
        session()->put('clienti_export.'.StrutturaCorrente::getId(), $request->only(['q', 'tipo_cliente', 'page']));
        $summary = $this->buildSummaryMetrics($query);

        $customers = $query
            ->withCount('schedine')
            ->withMax('schedine as last_arrive_at', 'arrive')
            ->orderBy('surname')
            ->orderBy('name')
            ->paginate(15)
            ->appends($request->only(['q', 'tipo_cliente']));

        $customers->getCollection()->transform(fn (Customers $customer) => $this->decorateCustomer($customer));

        return view('customers.export', [
            'customers' => $customers,
            'tipiClienti' => TipoCliente::query()->diSistema()->orderBy('id')->get(['descrizione']),
            'totaleFiltrati' => $summary['totaleFiltrati'],
            'totaleConEmail' => $summary['totaleConEmail'],
            'totaleConCellulare' => $summary['totaleConCellulare'],
            'totaleMarketing' => $summary['totaleMarketing'],
        ]);
    }

    public function destroy(Request $request, int $id)
    {
        $strutturaId = StrutturaCorrente::getId();
        abort_unless($strutturaId, 403, 'Seleziona una struttura prima di eliminare un cliente.');

        $deleted = DB::transaction(function () use ($id, $strutturaId): bool {
            $customer = Customers::query()->where('struttura_id', $strutturaId)
                ->lockForUpdate()->findOrFail($id);

            // Letture senza scope: anche un riferimento storico incoerente va preservato.
            $hasReferences = DB::table('schedina')->where('customer_id', $id)->exists()
                || DB::table('componenti')->where('customer_id', $id)->exists()
                || DB::table('customer_import_rows')->where(function ($query) use ($id) {
                    $query->where('imported_customer_id', $id)->orWhere('duplicate_customer_id', $id);
                })->exists()
                || DB::table('cestino_items')->where(function ($query) use ($id) {
                    $query->where('payload->customer_id', $id)
                        ->orWhere('payload->schedina->customer_id', $id);
                })->exists();

            if ($hasReferences) {
                return false;
            }

            app(CestinoService::class)->archiveModel($customer, ['source' => 'Clienti']);
            $customer->delete();
            return true;
        });

        $filters = $request->only(['q', 'tipo_cliente']);

        return redirect()->route('customer.export.index', $filters)
            ->with($deleted ? 'success' : 'warning', $deleted
                ? 'Cliente spostato nel cestino.'
                : 'Cliente non eliminato: esistono dati storici o collegamenti applicativi da preservare.');
    }

    public function exportCsv(Request $request)
    {
        $mode = (string) $request->input('mode', 'general');
        $rows = $this->buildFilteredQuery($request)
            ->withCount('schedine')
            ->withMax('schedine as last_arrive_at', 'arrive')
            ->orderBy('surname')
            ->orderBy('name')
            ->get()
            ->map(fn (Customers $customer) => $this->decorateCustomer($customer));

        [$filename, $headers, $lines] = match ($mode) {
            'email' => $this->buildEmailExport($rows),
            'whatsapp' => $this->buildWhatsappExport($rows),
            'postal' => $this->buildPostalExport($rows),
            default => $this->buildGeneralExport($rows),
        };

        array_unshift($lines, $this->csvLine($headers));
        $csv = implode("\n", $lines);

        return response($csv)
            ->header('Content-Type', 'text/csv; charset=UTF-8')
            ->header('Content-Disposition', 'attachment; filename="' . $filename . '"');
    }

    private function buildFilteredQuery(Request $request): Builder
    {
        // I vecchi parametri non devono influire né propagarsi come filtri nascosti.
        foreach (['group', 'subgroup', 'subgroup1', 'country', 'city', 'stato', 'privacy_consent', 'marketing_consent', 'communication_consent', 'channel', 'has_soggiorni'] as $field) {
            $request->query->remove($field);
            $request->request->remove($field);
        }
        $request->validate([
            'q' => ['nullable', 'string', 'max:191'],
            'tipo_cliente' => ['nullable', 'string', \Illuminate\Validation\Rule::in(TipoCliente::query()->diSistema()->pluck('descrizione')->all())],
            'page' => ['nullable', 'integer', 'min:1'],
        ]);
        $q = trim((string) $request->input('q', ''));
        $tipo = trim((string) $request->input('tipo_cliente', ''));

        return Customers::query()
            ->when($tipo !== '', fn (Builder $query) => $query->where('type_housed', $tipo))
            ->when($q !== '', function (Builder $query) use ($q) {
                $like = '%' . $q . '%';
                $query->where(function (Builder $inner) use ($like) {
                    $inner->where('name', 'like', $like)
                        ->orWhere('surname', 'like', $like)
                        ->orWhere('email', 'like', $like)
                        ->orWhere('cellphone', 'like', $like)
                        ->orWhere('phone', 'like', $like);
                });
            });
    }

    private function buildSummaryMetrics(Builder $query): array
    {
        return [
            'totaleFiltrati' => (clone $query)->count(),
            'totaleConEmail' => (clone $query)->whereNotNull('email')->where('email', '<>', '')->count(),
            'totaleConCellulare' => (clone $query)->whereNotNull('cellphone')->where('cellphone', '<>', '')->count(),
            'totaleMarketing' => (clone $query)->where('marketing_consent', true)->count(),
        ];
    }

    private function decorateCustomer(Customers $customer): Customers
    {
        $customer->setAttribute('display_country', $this->resolveGeoLabel($customer->country, 'country'));
        $customer->setAttribute('display_city', $this->resolveGeoLabel($customer->city, 'city'));
        $customer->setAttribute('full_name', trim(($customer->surname ?? '') . ' ' . ($customer->name ?? '')));
        return $customer;
    }

    private function resolveGeoLabel($value, string $field): string
    {
        $raw = trim((string) $value);
        if ($raw === '') {
            return '';
        }

        if (!ctype_digit($raw)) {
            return $raw;
        }

        return match ($field) {
            'country' => (string) (GeoNazione::query()->whereKey((int) $raw)->value('nome') ?? $raw),
            'city' => (string) (GeoComune::query()->whereKey((int) $raw)->value('nome') ?? $raw),
            default => $raw,
        };
    }

    private function buildGeneralExport(Collection $rows): array
    {
        $headers = ['Codice', 'Nome', 'Cognome', 'Tipo cliente', 'Nazione', 'Citta', 'Gruppo I', 'Gruppo II', 'Gruppo III', 'Email', 'Telefono', 'Cellulare', 'Privacy', 'Marketing', 'Comunicazioni', 'Soggiorni', 'Ultimo arrivo'];
        $lines = $rows->map(fn (Customers $c) => $this->csvLine([
            $c->numero_cliente,
            $c->name,
            $c->surname,
            $c->type_housed,
            $c->display_country,
            $c->display_city,
            $c->group,
            $c->subgroup,
            $c->subgroup1,
            $c->email,
            $c->phone,
            $c->cellphone,
            $c->privacy_consent ? 'SI' : 'NO',
            $c->marketing_consent ? 'SI' : 'NO',
            $c->communication_consent ? 'SI' : 'NO',
            (string) ($c->schedine_count ?? 0),
            $c->last_arrive_at ?: '',
        ]))->all();

        return ['clienti_export_generale.csv', $headers, $lines];
    }

    private function buildEmailExport(Collection $rows): array
    {
        $headers = ['Codice', 'Nome', 'Cognome', 'Email'];
        $lines = $rows
            ->filter(fn (Customers $c) => $c->marketing_consent && !empty($c->email))
            ->map(fn (Customers $c) => $this->csvLine([$c->numero_cliente, $c->name, $c->surname, $c->email]))
            ->values()
            ->all();

        return ['clienti_export_email.csv', $headers, $lines];
    }

    private function buildWhatsappExport(Collection $rows): array
    {
        $headers = ['Codice', 'Nome', 'Cognome', 'Cellulare'];
        $lines = $rows
            ->filter(fn (Customers $c) => $c->communication_consent && !empty($c->cellphone))
            ->map(fn (Customers $c) => $this->csvLine([$c->numero_cliente, $c->name, $c->surname, $c->cellphone]))
            ->values()
            ->all();

        return ['clienti_export_whatsapp.csv', $headers, $lines];
    }

    private function buildPostalExport(Collection $rows): array
    {
        $headers = ['Codice', 'Nome completo', 'Indirizzo', 'CAP', 'Citta', 'Provincia', 'Nazione'];
        $lines = $rows
            ->filter(fn (Customers $c) => $c->privacy_consent && (!empty($c->address) || !empty($c->number)))
            ->map(fn (Customers $c) => $this->csvLine([
                $c->numero_cliente,
                trim(($c->surname ?? '') . ' ' . ($c->name ?? '')),
                trim(collect([$c->typeaway, $c->address, $c->number])->filter()->implode(' ')),
                $c->cap,
                $c->display_city,
                $c->province,
                $c->display_country,
            ]))
            ->values()
            ->all();

        return ['clienti_export_postale.csv', $headers, $lines];
    }

    private function csvLine(array $values): string
    {
        $escaped = array_map(function ($value) {
            $text = (string) $value;
            $text = str_replace('"', '""', $text);
            return '"' . $text . '"';
        }, $values);

        return implode(',', $escaped);
    }
}
