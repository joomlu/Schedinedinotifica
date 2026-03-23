<?php

namespace App\Http\Controllers;

use App\Models\Customers;
use App\Models\GeoComune;
use App\Models\GeoNazione;
use App\Models\Gruppo;
use App\Models\TipoCliente;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;

class CustomerExportController extends Controller
{
    public function index(Request $request)
    {
        $query = $this->buildFilteredQuery($request);
        $summary = $this->buildSummaryMetrics($query);

        $customers = $query
            ->withCount('schedine')
            ->withMax('schedine as last_arrive_at', 'arrive')
            ->orderBy('surname')
            ->orderBy('name')
            ->paginate(15)
            ->withQueryString();

        $customers->getCollection()->transform(fn (Customers $customer) => $this->decorateCustomer($customer));

        return view('customers.export', [
            'customers' => $customers,
            'tipiClienti' => TipoCliente::query()
                ->when(method_exists(TipoCliente::query()->getModel(), 'getTable'), fn ($query) => $query)
                ->orderBy('descrizione')
                ->get(['descrizione']),
            'gruppiLivello1' => Gruppo::query()->where('livello', 1)->orderBy('nome')->get(['nome']),
            'gruppiLivello2' => Gruppo::query()->where('livello', 2)->orderBy('nome')->get(['nome']),
            'gruppiLivello3' => Gruppo::query()->where('livello', 3)->orderBy('nome')->get(['nome']),
            'nazioni' => GeoNazione::query()->orderBy('nome')->get(['nome']),
            'citta' => GeoComune::query()->orderBy('nome')->get(['nome']),
            'totaleFiltrati' => $summary['totaleFiltrati'],
            'totaleConEmail' => $summary['totaleConEmail'],
            'totaleConCellulare' => $summary['totaleConCellulare'],
            'totaleMarketing' => $summary['totaleMarketing'],
        ]);
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
        $q = trim((string) $request->input('q', ''));
        $tipo = trim((string) $request->input('tipo_cliente', ''));
        $group = trim((string) $request->input('group', ''));
        $subgroup = trim((string) $request->input('subgroup', ''));
        $subgroup1 = trim((string) $request->input('subgroup1', ''));
        $country = trim((string) $request->input('country', ''));
        $city = trim((string) $request->input('city', ''));
        $stato = trim((string) $request->input('stato', ''));
        $privacy = $request->input('privacy_consent', '');
        $marketing = $request->input('marketing_consent', '');
        $communication = $request->input('communication_consent', '');
        $channel = trim((string) $request->input('channel', ''));
        $hasSoggiorni = trim((string) $request->input('has_soggiorni', ''));

        $query = Customers::query()
            ->when($tipo !== '', fn (Builder $query) => $query->where('type_housed', $tipo))
            ->when($group !== '', fn (Builder $query) => $query->where('group', $group))
            ->when($subgroup !== '', fn (Builder $query) => $query->where('subgroup', $subgroup))
            ->when($subgroup1 !== '', fn (Builder $query) => $query->where('subgroup1', $subgroup1))
            ->when($q !== '', function (Builder $query) use ($q) {
                $like = '%' . $q . '%';
                $query->where(function (Builder $inner) use ($like) {
                    $inner->where('numero_cliente', 'like', $like)
                        ->orWhere('name', 'like', $like)
                        ->orWhere('surname', 'like', $like)
                        ->orWhere('email', 'like', $like)
                        ->orWhere('cellphone', 'like', $like)
                        ->orWhere('phone', 'like', $like);
                });
            });

        if ($country !== '') {
            $this->applyGeoFilter($query, 'country', $country, GeoNazione::query(), 'nome');
        }

        if ($city !== '') {
            $this->applyGeoFilter($query, 'city', $city, GeoComune::query(), 'nome');
        }

        $this->applyStateFilter($query, $stato);
        $this->applyConsentFilter($query, 'privacy_consent', $privacy);
        $this->applyConsentFilter($query, 'marketing_consent', $marketing);
        $this->applyConsentFilter($query, 'communication_consent', $communication);

        if ($channel === 'email') {
            $query->where('marketing_consent', true)->whereNotNull('email')->where('email', '<>', '');
        }
        if ($channel === 'whatsapp') {
            $query->where('communication_consent', true)->whereNotNull('cellphone')->where('cellphone', '<>', '');
        }
        if ($channel === 'postal') {
            $query->where('privacy_consent', true)
                ->whereNotNull('address')->where('address', '<>', '')
                ->whereNotNull('city')->where('city', '<>', '');
        }

        if ($hasSoggiorni === 'yes') {
            $query->has('schedine');
        }
        if ($hasSoggiorni === 'no') {
            $query->doesntHave('schedine');
        }

        return $query;
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

    private function applyGeoFilter(Builder $query, string $column, string $value, Builder $sourceQuery, string $labelColumn): void
    {
        $raw = trim($value);
        if ($raw === '') {
            return;
        }

        $variants = collect([$raw]);

        if (ctype_digit($raw)) {
            $variants->push((string) ((int) $raw));
        }

        $record = (clone $sourceQuery)
            ->where(function (Builder $inner) use ($labelColumn, $raw) {
                $inner->where($labelColumn, $raw);
                if (is_numeric($raw)) {
                    $inner->orWhereKey((int) $raw);
                }
            })
            ->first(['id', $labelColumn]);

        if ($record) {
            $variants->push((string) $record->{$labelColumn});
            $variants->push((string) $record->getKey());
        }

        $query->whereIn($column, $variants->filter()->unique()->values()->all());
    }

    private function applyStateFilter(Builder $query, string $stato): void
    {
        if ($stato === 'bozza') {
            $query->where(function (Builder $inner) {
                $inner->whereNull('name')
                    ->orWhere('name', '')
                    ->orWhereNull('surname')
                    ->orWhere('surname', '')
                    ->orWhereNull('type_housed')
                    ->orWhere('type_housed', '')
                    ->orWhere(function (Builder $docMissing) {
                        $docMissing
                            ->whereIn('type_housed', ['Ospite', 'Componente'])
                            ->where(function (Builder $required) {
                                $required->whereNull('nac_reg')
                                    ->orWhere('nac_reg', '')
                                    ->orWhereNull('type_doc_reg')
                                    ->orWhere('type_doc_reg', '')
                                    ->orWhereNull('num_doc_reg')
                                    ->orWhere('num_doc_reg', '');
                            });
                    });
            });
        }

        if ($stato === 'completo') {
            $query->whereNotNull('name')
                ->where('name', '<>', '')
                ->whereNotNull('surname')
                ->where('surname', '<>', '')
                ->whereNotNull('type_housed')
                ->where('type_housed', '<>', '')
                ->where(function (Builder $complete) {
                    $complete->whereNotIn('type_housed', ['Ospite', 'Componente'])
                        ->orWhere(function (Builder $docReady) {
                            $docReady->whereIn('type_housed', ['Ospite', 'Componente'])
                                ->whereNotNull('nac_reg')
                                ->where('nac_reg', '<>', '')
                                ->whereNotNull('type_doc_reg')
                                ->where('type_doc_reg', '<>', '')
                                ->whereNotNull('num_doc_reg')
                                ->where('num_doc_reg', '<>', '');
                        });
                });
        }
    }

    private function applyConsentFilter(Builder $query, string $column, mixed $value): void
    {
        if ($value === '' || $value === null) {
            return;
        }

        $query->where($column, in_array((string) $value, ['1', 'yes'], true));
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
