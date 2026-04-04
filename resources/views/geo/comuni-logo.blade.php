@extends('layouts.master')

@section('title') Geo Comuni - Logo @endsection

@section('content')
    @component('components.breadcrumb')
        @slot('li_1') Geo @endslot
        @slot('title') Logo Comuni @endslot
    @endcomponent

    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-body">
                    @if(session('success'))
                        <div class="alert alert-success">{{ session('success') }}</div>
                    @endif
                    @if($errors->any())
                        <div class="alert alert-danger">
                            <ul class="mb-0">
                                @foreach($errors->all() as $error)
                                    <li>{{ $error }}</li>
                                @endforeach
                            </ul>
                        </div>
                    @endif

                    <form method="GET" action="{{ route('geo.comuni.logo') }}" class="mb-3">
                        <x-table-topbar
                            title="Geo Comuni"
                            subtitle="Carica stemma/logo del Comune"
                            searchPlaceholder="Cerca per nome o codice ISTAT"
                            searchId="geo-comuni-search"
                        />
                        <input type="hidden" name="q" value="{{ $q }}" id="geo-comuni-hidden-q">
                    </form>

                    <div class="table-responsive">
                        <table class="table align-middle table-striped">
                            <thead>
                                <tr>
                                    <th>Comune</th>
                                    <th>Provincia</th>
                                    <th>Codice ISTAT</th>
                                    <th>Logo attuale</th>
                                    <th class="text-end">Azione</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($comuni as $comune)
                                    <tr>
                                        <td class="fw-semibold">{{ $comune->nome }}</td>
                                        <td>{{ optional($comune->provincia)->sigla }}</td>
                                        <td>{{ $comune->codice_istat }}</td>
                                        <td>
                                            @php $logoComune = $comune->logo_citta ?? $comune->logo; @endphp
                                            @if($logoComune)
                                                <img src="{{ asset($logoComune) }}" alt="Logo {{ $comune->nome }}" style="max-height:50px;" class="img-fluid">
                                            @else
                                                <span class="text-muted">Nessun logo</span>
                                            @endif
                                        </td>
                                        <td class="text-end">
                                            <form method="POST" action="{{ route('geo.comuni.logo.store', $comune->id) }}" enctype="multipart/form-data" class="d-inline-flex align-items-center gap-2">
                                                @csrf
                                                <input type="file" name="logo" accept="image/png,image/jpeg,image/webp" class="form-control form-control-sm" required>
                                                <button type="submit" class="btn btn-primary btn-sm">Carica</button>
                                            </form>
                                            @if($logoComune)
                                                <form method="POST" action="{{ route('geo.comuni.logo.destroy', $comune->id) }}" class="d-inline" data-confirm-label="{{ 'il logo del comune di ' . $comune->nome }}">
                                                    @csrf
                                                    @method('DELETE')
                                                    <button type="submit" class="btn btn-outline-danger btn-sm">Rimuovi</button>
                                                </form>
                                            @endif
                                        </td>
                                    </tr>
                                @empty
                                    <tr><td colspan="5" class="text-center text-muted">Nessun comune trovato</td></tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>

                    <div class="mt-3">
                        {{ $comuni->links() }}
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection

@section('script')
<script>
    document.addEventListener('DOMContentLoaded', function () {
        const input = document.getElementById('geo-comuni-search');
        const hidden = document.getElementById('geo-comuni-hidden-q');
        if (input && hidden) {
            input.setAttribute('name', 'q');
            input.value = hidden.value || '';
            input.addEventListener('input', () => hidden.value = input.value);
            const clearBtn = document.getElementById('geo-comuni-search-clear');
            if (clearBtn) {
                clearBtn.addEventListener('click', () => {
                    input.value = '';
                    hidden.value = '';
                });
            }
        }
    });
</script>
@endsection
