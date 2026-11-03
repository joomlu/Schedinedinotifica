@extends('layouts.master')
@section('title', 'Cestino')
@section('content')
@component('components.breadcrumb')
    @slot('li_1') Sistema @endslot
    @slot('title') Cestino @endslot
@endcomponent

<div class="d-flex flex-wrap gap-2 align-items-center mb-3">
    <form id="cestinoSelezione" action="{{ route('cestino.destroySelected') }}" method="POST"
          data-confirm-title="Elimina definitivamente gli elementi selezionati?" data-confirm-label="gli elementi selezionati dal cestino">
        @csrf @method('DELETE')
        <input type="hidden" name="conferma" value="1">
        <button id="cestinoEliminaSelezionati" class="btn btn-danger" type="submit" disabled>Elimina selezionati (<span id="cestinoConteggio">0</span>)</button>
    </form>
    <form action="{{ route('cestino.empty') }}" method="POST"
          data-confirm-title="Svuota il cestino del contesto corrente?" data-confirm-label="tutti gli elementi eliminabili del cestino corrente, anche nelle altre pagine e fuori dai filtri">
        @csrf @method('DELETE')
        <input type="hidden" name="conferma" value="1">
        <button class="btn btn-outline-danger" type="submit" @disabled(!$eliminabili)>Svuota cestino ({{ $eliminabili }})</button>
    </form>
    <span class="text-muted small">Eliminazione definitiva. Lo svuotamento comprende tutte le pagine del contesto corrente; i dati di sistema protetti restano conservati.</span>
</div>
<x-crud-table title="Cestino" subtitle="Elementi eliminati nel contesto autorizzato: consulta, ripristina o elimina definitivamente."
    searchPlaceholder="Cerca per tipo, codice, titolo o origine..." searchId="cestinoSearch" createText="" createTarget="#" tableId="cestinoTable" :paginator="$items">
    <x-slot name="topbarLeft">
        <form id="cestinoFiltri" method="GET" action="{{ route('cestino.index') }}" class="d-flex align-items-center gap-2 flex-wrap">
            <input type="hidden" name="q" value="{{ request('q') }}">
            <label class="visually-hidden" for="cestinoTipoFilter">Sezione</label>
            <select name="tipo" id="cestinoTipoFilter" class="form-select" style="max-width:220px">
                <option value="">Tutte le sezioni</option>
                @foreach($tipi as $tipo)<option value="{{ $tipo }}" @selected($tipoSelezionato === $tipo)>{{ $tipo }}</option>@endforeach
            </select>
            <label class="visually-hidden" for="cestinoQuantita">Elementi per pagina</label>
            <select id="cestinoQuantita" name="per_page" class="form-select" style="max-width:160px">
                @foreach([10,25,50,100] as $quantity)<option value="{{ $quantity }}" @selected((int)request('per_page',10) === $quantity)>{{ $quantity }} per pagina</option>@endforeach
            </select>
            @if($tipoSelezionato !== '')<a href="{{ route('cestino.index', request()->only(['q','per_page'])) }}" class="btn btn-light">Reset sezione</a>@endif
        </form>
    </x-slot>
    <x-slot name="columns">
        <tr>
            <th><input type="checkbox" id="cestinoSelezionaPagina" class="form-check-input" aria-label="Seleziona gli elementi eliminabili di questa pagina"></th>
            <th>Eliminato il</th><th>Sezione</th><th>Codice</th><th>Titolo</th><th>Origine</th><th>Circuito</th><th class="text-end">Azioni</th>
        </tr>
    </x-slot>
    <x-slot name="rows">
        @foreach($items as $item)
            <tr class="data-row">
                <td>@unless($item->admin_read_only)<input type="checkbox" class="form-check-input cestino-selezione" name="ids[]" value="{{ $item->id }}" form="cestinoSelezione" aria-label="Seleziona {{ $item->title ?: 'elemento '.$item->id }}">@endunless</td>
                <td class="text-nowrap">{{ optional($item->deleted_at)->format('d/m/Y H:i') }}</td>
                <td>{{ $item->sezione }}</td><td>{{ $item->code ?: '—' }}</td><td>{{ $item->title ?: '—' }}</td><td>{{ $item->source ?: '—' }}</td><td>{{ $item->circuito ?: '—' }}</td>
                <td class="text-end"><div class="d-inline-flex gap-1">
                    <button type="button" class="btn btn-soft-info" title="Dettaglio" aria-label="Visualizza dettaglio {{ $item->title }}" data-bs-toggle="modal" data-bs-target="#cestinoDettaglio{{ $item->id }}"><i class="ri-eye-line" aria-hidden="true"></i></button>
                    @unless($item->admin_read_only)
                        <form action="{{ route('cestino.restore', $item->id) }}" method="POST" class="d-inline">@csrf<button type="submit" class="btn btn-soft-success" title="Ripristina" aria-label="Ripristina {{ $item->title }}"><i class="ri-arrow-go-back-line" aria-hidden="true"></i></button></form>
                        <form action="{{ route('cestino.destroy', $item->id) }}" method="POST" class="d-inline" data-confirm-title="Elimina definitivamente dal cestino?" data-confirm-label="{{ $item->title ?: 'questo elemento del cestino' }}">
                            @csrf @method('DELETE')<button type="submit" class="btn btn-soft-danger" title="Elimina definitivamente" aria-label="Elimina definitivamente {{ $item->title }}"><i class="ri-delete-bin-line" aria-hidden="true"></i></button>
                        </form>
                    @endunless
                </div></td>
            </tr>
        @endforeach
    </x-slot>
</x-crud-table>

@foreach($items as $item)
    <div id="cestinoDettaglio{{ $item->id }}" class="modal fade" tabindex="-1" aria-labelledby="cestinoTitolo{{ $item->id }}" aria-hidden="true">
        <div class="modal-dialog modal-lg modal-dialog-scrollable"><div class="modal-content">
            <div class="modal-header"><h5 id="cestinoTitolo{{ $item->id }}" class="modal-title">{{ $item->title ?: 'Dettaglio elemento' }}</h5><button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Chiudi"></button></div>
            <div class="modal-body">
                <dl class="row mb-3"><dt class="col-sm-4">Sezione</dt><dd class="col-sm-8">{{ $item->sezione }}</dd><dt class="col-sm-4">Tipo</dt><dd class="col-sm-8">{{ $item->entity_type }}</dd><dt class="col-sm-4">Codice</dt><dd class="col-sm-8">{{ $item->code ?: 'Non indicato' }}</dd><dt class="col-sm-4">Eliminato il</dt><dd class="col-sm-8">{{ optional($item->deleted_at)->format('d/m/Y H:i') }}</dd></dl>
                <h6>Dati dell’elemento eliminato</h6>
                @if(count($item->display_rows))
                    <dl class="row mb-0">@foreach($item->display_rows as $row)<dt class="col-sm-4 text-break">{{ $row['etichetta'] }}</dt><dd class="col-sm-8 text-break" style="white-space:pre-wrap">{{ $row['valore'] }}</dd>@endforeach</dl>
                @else<p class="text-muted">Non sono presenti ulteriori dati descrittivi disponibili.</p>@endif
            </div>
            <div class="modal-footer"><button type="button" class="btn btn-light" data-bs-dismiss="modal">Chiudi</button>@unless($item->admin_read_only)<form action="{{ route('cestino.restore', $item->id) }}" method="POST">@csrf<button type="submit" class="btn btn-primary">Ripristina</button></form>@endunless</div>
        </div></div>
    </div>
@endforeach
@endsection
@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function () {
    const boxes = [...document.querySelectorAll('.cestino-selezione')];
    const all = document.getElementById('cestinoSelezionaPagina');
    const button = document.getElementById('cestinoEliminaSelezionati');
    const refresh = () => {
        const count = boxes.filter(box => box.checked).length;
        document.getElementById('cestinoConteggio').textContent = String(count);
        button.disabled = count === 0;
        all.checked = boxes.length > 0 && count === boxes.length;
        all.indeterminate = count > 0 && count < boxes.length;
    };
    all.addEventListener('change', () => { boxes.forEach(box => { box.checked = all.checked; }); refresh(); });
    boxes.forEach(box => box.addEventListener('change', refresh));
    ['cestinoTipoFilter', 'cestinoQuantita'].forEach(id => document.getElementById(id).addEventListener('change', event => event.target.form.submit()));
    refresh();
});
</script>
@endpush
