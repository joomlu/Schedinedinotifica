@extends('layouts.master')
@section('title') @lang('translation.Titolo') @endsection
@section('css')
@endsection
@section('content')
@component('components.breadcrumb')
@slot('li_1') Tabelle @endslot
@slot('title')Titolo @endslot
@endcomponent

<div class="row mb-3">
    <div class="col-12 text-end">
        <button type="button" class="btn btn-soft-primary" data-bs-toggle="modal" data-bs-target="#myModal">
            <i class="ri-add-circle-line align-middle me-1"></i> @lang('translation.nuovo_titolo')
        </button>
    </div>
</div>
<!-- Modal Nuovo Titolo -->
<div id="myModal" class="modal fade" tabindex="-1" aria-labelledby="myModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="myModalLabel">@lang('translation.nuovo_titolo')</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form method="POST" action="{{ route('titolo.store') }}">
                @csrf
                <div class="modal-body">
                    <div class="mb-3">
                        <label for="nomeInput" class="form-label">@lang('translation.nome_titolo')</label>
                        <input type="text" name="nome" class="form-control" id="nomeInput" required>
                    </div>
                    <div class="mb-3">
                        <label for="descrizioneInput" class="form-label">@lang('translation.descrizione_titolo')</label>
                        <input type="text" name="descrizione" class="form-control" id="descrizioneInput" required>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-light" data-bs-dismiss="modal">Chiudi</button>
                    <button type="submit" class="btn btn-soft-primary">Salva</button>
                </div>
            </form>
        </div>
    </div>
</div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-light" data-bs-dismiss="modal">Close</button>
                <button type="submit" class="btn btn-primary ">Salvar</button>
</form>
            </div>
      
        </div><!-- /.modal-content -->
    </div><!-- /.modal-dialog -->
</div><!-- /.modal -->


                                                </div>
                                            </div>

<div class="row">
    <div class="col-lg-12">
        <div class="card">
            <div class="card-header">
                <h5 class="card-title mb-0">@lang('translation.Titolo')</h5>
            </div>
            <div class="card-body">
                <x-ui.datatable class="table-bordered align-middle">
                    <x-slot name="head">
                        <tr>
                            <th>@lang('translation.nome_titolo')</th>
                            <th>@lang('translation.descrizione_titolo')</th>
                            <th class="text-end" style="width:120px;">Azioni</th>
                        </tr>
                    </x-slot>
                    <x-slot name="body">
                        @forelse($titoli as $titolo)
                            <tr>
                                <td>{{ $titolo->nome }}</td>
                                <td>{{ $titolo->descrizione }}</td>
                                <td class="text-end">
                                    <button type="button" class="btn btn-soft-info btn-sm" data-bs-toggle="modal" data-bs-target="#ModalEdit{{ $titolo->id }}">
                                        Modifica
                                    </button>
                                    <form action="{{ route('titolo.destroy', $titolo->id) }}" method="POST" class="d-inline">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="btn btn-soft-danger btn-sm" onclick="return confirm('Sei sicuro di voler eliminare questo titolo?')">
                                            Elimina
                                        </button>
                                    </form>
                                </td>
                            </tr>
                            <div id="ModalEdit{{ $titolo->id }}" class="modal fade" tabindex="-1" aria-labelledby="myModalEditLabel{{ $titolo->id }}" aria-hidden="true">
                                <div class="modal-dialog">
                                    <div class="modal-content">
                                        <div class="modal-header">
                                            <h5 class="modal-title" id="myModalEditLabel{{ $titolo->id }}">@lang('translation.modifica_titolo')</h5>
                                            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                        </div>
                                        <form method="POST" action="{{ route('titolo.update', $titolo->id) }}">
                                            @csrf
                                            @method('PUT')
                                            <div class="modal-body">
                                                <div class="mb-3">
                                                    <label for="nomeInputEdit{{ $titolo->id }}" class="form-label">@lang('translation.nome_titolo')</label>
                                                    <input type="text" name="nome" value="{{ $titolo->nome }}" class="form-control" id="nomeInputEdit{{ $titolo->id }}" required>
                                                </div>
                                                <div class="mb-3">
                                                    <label for="descrizioneInputEdit{{ $titolo->id }}" class="form-label">@lang('translation.descrizione_titolo')</label>
                                                    <input type="text" name="descrizione" value="{{ $titolo->descrizione }}" class="form-control" id="descrizioneInputEdit{{ $titolo->id }}" required>
                                                </div>
                                            </div>
                                            <div class="modal-footer">
                                                <button type="button" class="btn btn-light" data-bs-dismiss="modal">Chiudi</button>
                                                <button type="submit" class="btn btn-soft-primary">Salva</button>
                                            </div>
                                        </form>
                                    </div>
                                </div>
                            </div>
                        @empty
                            <tr>
                                <td colspan="3" class="text-center text-muted">@lang('translation.nessun_titolo')</td>
                            </tr>
                        @endforelse
                    </x-slot>
                </x-ui.datatable>
                    <div class="row align-items-center mt-3">
                        <div class="col-sm-12 col-md-5">
                            <p class="mb-0 text-muted">
                                Mostrando
                                <span class="fw-semibold">{{ $titoli->firstItem() }}</span>
                                a
                                <span class="fw-semibold">{{ $titoli->lastItem() }}</span>
                                di
                                <span class="fw-semibold">{{ $titoli->total() }}</span>
                                risultati
                            </p>
                        </div>
                        <div class="col-sm-12 col-md-7">
                            <div class="d-flex justify-content-end">
                                {{ $titoli->links('pagination::bootstrap-5') }}
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>



@endsection
@section('script')
<script src="{{ URL::asset('build/js/pages/datatables.init.js') }}"></script>
@endsection