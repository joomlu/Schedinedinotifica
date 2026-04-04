@extends('layouts.master')
@section('title')
	@lang('translation.strutture')
@endsection
@section('content')
	@component('components.breadcrumb')
		@slot('li_1')
			Anagrafica
		@endslot
		@slot('title')
			Strutture Ricettive
		@endslot
	@endcomponent
	<div class="alert alert-info">Questa pagina non è più disponibile. Gestire la struttura principale da <a href="{{ route('struttura.edit') }}">qui</a>.</div>
@endsection
@extends('layouts.master')
@section('title')
	@lang('translation.strutture')
@endsection
@section('css')
	<link href="{{ URL::asset('build/libs/sweetalert2/sweetalert2.min.css') }}" rel="stylesheet" type="text/css" />
@endsection
@section('content')
	@component('components.breadcrumb')
		@slot('li_1')
			Anagrafica
		@endslot
		@slot('title')
			Strutture Ricettive
		@endslot
	@endcomponent
	<div class="row">
		<div class="col-lg-12">
			<div class="card">
				<div class="card-header">
					<div class="d-flex align-items-center flex-wrap gap-2">
						<div class="flex-grow-1">
							<a href="{{ route('struttura.create') }}" class="btn btn-primary add-btn">
								<i class="ri-add-fill me-1 align-bottom"></i> Nuova Struttura
							</a>
						</div>
						<div class="flex-shrink-0">
							<div class="hstack text-nowrap gap-2">
								<button class="btn btn-soft-danger" id="remove-actions" onClick="deleteMultiple()"><i class="ri-delete-bin-2-line"></i></button>
								<button class="btn btn-soft-primary"><i class="ri-filter-2-line me-1 align-bottom"></i> Filtri</button>
							</div>
						</div>
					</div>
				</div>
			</div>
		</div>
		<div class="col-xxl-12">
			<div class="card" id="strutturaList">
				<div class="card-body">
					<div class="table-responsive table-card mb-3">
						<table class="table align-middle table-nowrap mb-0" id="strutturaTable">
							<thead class="table-light">
								<tr>
									<th scope="col" style="width: 50px;">
										<div class="form-check">
											<input class="form-check-input" type="checkbox" id="checkAll" value="option">
										</div>
									</th>
									<th>Nome Struttura</th>
									<th>CIR</th>
									<th>Tipologia</th>
									<th>Classificazione</th>
									<th>Città</th>
									<th>Regione</th>
									<th>Telefono</th>
									<th>Email</th>
									<th>Azioni</th>
								</tr>
							</thead>
							<tbody>
								@forelse($strutture as $struttura)
									<tr>
										<td>
											<div class="form-check">
												<input class="form-check-input" type="checkbox" name="chk_child" value="{{ $struttura->id }}">
											</div>
										</td>
										<td>{{ $struttura->nome_struttura }}</td>
										<td>{{ $struttura->cir }}</td>
										<td>{{ $struttura->tipologia_generale }}<br><small>{{ $struttura->tipologia_struttura }}</small></td>
										<td>{{ $struttura->classificazione }}</td>
										<td>{{ $struttura->città }}</td>
										<td>{{ $struttura->regione }}</td>
										<td>{{ $struttura->telefono }}</td>
										<td>{{ $struttura->email }}</td>
										<td>
											<ul class="list-inline hstack gap-2 mb-0">
												<li class="list-inline-item">
													<a href="{{ route('struttura.edit', $struttura->id) }}" class="text-primary" title="Modifica"><i class="ri-edit-2-line fs-16"></i></a>
												</li>
												<li class="list-inline-item">
													<form action="{{ route('struttura.destroy', $struttura->id) }}" method="POST" style="display:inline;" data-confirm-label="{{ 'la struttura ' . $struttura->nome_struttura }}">
														@csrf
														@method('DELETE')
														<button type="submit" class="btn btn-link p-0 m-0 align-baseline text-danger" title="Elimina">
															<i class="ri-delete-bin-2-line fs-16"></i>
														</button>
													</form>
												</li>
											</ul>
										</td>
									</tr>
								@empty
									<tr>
										<td colspan="10" class="text-center">Nessuna struttura presente.</td>
									</tr>
								@endforelse
							</tbody>
						</table>
					</div>
					<div class="mt-3">
						{{ $strutture->links() }}
					</div>
				</div>
			</div>
		</div>
	</div>
@endsection
