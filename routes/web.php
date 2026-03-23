
<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use App\Http\Controllers\GeoController;
use App\Http\Controllers\GeoComuneLogoController;
use App\Http\Controllers\TassaDiSoggiornoController;
use App\Http\Controllers\TassaEsenzioneController;
use App\Http\Controllers\TassaReportController;
use App\Http\Controllers\TipoAlloggiatoController;
use App\Http\Controllers\TipoClienteController;
use App\Http\Controllers\TipoDocumentoController;
use App\Http\Controllers\StrutturaController;
use App\Http\Controllers\StrutturaSelezioneController;
use App\Http\Controllers\StrutturaUserController;
use App\Http\Controllers\RilasciatoDaController;
use App\Http\Controllers\ArrivalsController;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\ArchivosController;
use App\Http\Controllers\CustomerController;
use App\Http\Controllers\CustomerExportController;
use App\Http\Controllers\SchedinaController;
use App\Http\Controllers\GroupController;
use App\Http\Controllers\TitleController;
use App\Http\Controllers\TipoViaController;
use App\Http\Controllers\LocationController;
use App\Http\Controllers\Superadmin\AmministratoriController;
use App\Http\Controllers\Superadmin\ArticoliController;
use App\Http\Controllers\Superadmin\ProprietariController as SuperadminProprietariController;
use App\Http\Controllers\Superadmin\StruttureController as SuperadminStruttureController;
use App\Http\Controllers\Superadmin\PagamentiController;
use App\Http\Controllers\Superadmin\ImpersonazioneController;
use App\Http\Controllers\Admin\ProprietariController as AdminProprietariController;
use App\Http\Controllers\Admin\PagamentiController as AdminPagamentiController;
use App\Http\Controllers\Admin\StruttureController as AdminStruttureController;
use App\Http\Controllers\Proprietario\StruttureController as ProprietarioStruttureController;
use App\Http\Controllers\QaController;
use App\Http\Controllers\QA\DemoMapController;
use App\Http\Controllers\CestinoController;
use App\Http\Controllers\CalendarioController;
use App\Http\Controllers\GestioneOperativaController;
use App\Http\Controllers\HelpCenterController;
use App\Http\Controllers\IstatTabellaAController;
use App\Http\Controllers\NotificheController;
use App\Http\Controllers\PresenzeController;
use App\Http\Controllers\QuesturaExportController;
use App\Http\Controllers\SupportoController;
use App\Http\Controllers\WebCheckinController;

Auth::routes();

Route::middleware(['auth'])->group(function () {
    // Fallback logout via GET to avoid 419 when no CSRF token is sent
    Route::get('/logout', function () {
        Auth::logout();
        request()->session()->invalidate();
        request()->session()->regenerateToken();

        return redirect()->route('login');
    })->name('logout.get');

    // Download Adwords
    Route::get('/adwords/files/{filename}', function ($filename) {
        $path = storage_path('app/uploads/adwords/' . $filename);

        if (!file_exists($path)) {
            abort(404);
        }

        return response()->file($path);
    })->name('adwords.files');

    // Geo endpoints
    Route::prefix('geo')->group(function () {
        Route::get('/nazioni', [GeoController::class, 'nazioni']);
        Route::get('/regioni', [GeoController::class, 'regioni']);
        Route::get('/province', [GeoController::class, 'province']);
        Route::get('/comuni', [GeoController::class, 'comuni']);
        Route::get('/cap', [GeoController::class, 'cap']);
        Route::get('/resolve', [GeoController::class, 'resolve']);
    });

    Route::prefix('geo')->middleware(['ruolo:super_admin,admin'])->group(function () {
        Route::get('/comuni/logo', [GeoComuneLogoController::class, 'index'])->name('geo.comuni.logo');
        Route::post('/comuni/{id}/logo', [GeoComuneLogoController::class, 'store'])->name('geo.comuni.logo.store');
        Route::delete('/comuni/{id}/logo', [GeoComuneLogoController::class, 'destroy'])->name('geo.comuni.logo.destroy');
    });

    // Configurazioni base
    Route::resource('tipo_alloggiato', TipoAlloggiatoController::class)->names('tipo_alloggiato');
    Route::resource('tipo_cliente', TipoClienteController::class)
        ->only(['index', 'store', 'update', 'destroy'])
        ->names('tipo_cliente');
    Route::resource('tipo_documento', TipoDocumentoController::class)->names('tipo_documento');

    Route::get('/tassa_di_soggiorno', [TassaDiSoggiornoController::class, 'edit'])->name('tassa_di_soggiorno.edit');
    Route::put('/tassa_di_soggiorno', [TassaDiSoggiornoController::class, 'update'])->name('tassa_di_soggiorno.update');
    Route::get('/tassa_di_soggiorno/create', function () {
        return redirect()->route('tassa_di_soggiorno.edit');
    });

    Route::get('/tassa_di_soggiorno/rapporto', [TassaReportController::class, 'index'])->name('tassa_di_soggiorno.rapporto');
    Route::get('/tassa_di_soggiorno/rapporto/csv', [TassaReportController::class, 'exportCsv'])->name('tassa_di_soggiorno.rapporto.csv');

    Route::resource('tassa_esenzioni', TassaEsenzioneController::class)->only(['store', 'update', 'destroy'])->names('tassa_esenzioni');

    Route::get('/struttura', [StrutturaController::class, 'edit'])->name('struttura.edit');
    Route::put('/struttura', [StrutturaController::class, 'update'])->name('struttura.update');
    Route::get('/struttura/zone-suggestions', [StrutturaController::class, 'zoneSuggestions'])->name('struttura.zone_suggestions');
    Route::get('/strutture/seleziona', [StrutturaSelezioneController::class, 'index'])->name('strutture.seleziona.index');
    Route::post('/strutture/{id}/seleziona', [StrutturaSelezioneController::class, 'seleziona'])->name('strutture.seleziona');

    // Gestione utenti per struttura corrente
    Route::get('/strutture/utenti', [StrutturaUserController::class, 'index'])->name('strutture.utenti.index');
    Route::get('/strutture/utenti/create', [StrutturaUserController::class, 'create'])->name('strutture.utenti.create');
    Route::post('/strutture/utenti', [StrutturaUserController::class, 'store'])->name('strutture.utenti.store');
    Route::post('/strutture/utenti/{id}/reset', [StrutturaUserController::class, 'resetPassword'])->name('strutture.utenti.reset');

    // Rilasciato da CRUD
    Route::resource('rilasciato', RilasciatoDaController::class)
        ->only(['index', 'store', 'update', 'destroy'])
        ->names('rilasciato');

    // SuperAdmin area
    Route::prefix('superadmin')->middleware(['ruolo:super_admin'])->group(function () {
        Route::get('/amministratori', [AmministratoriController::class, 'index'])->name('superadmin.amministratori.index');
        Route::get('/amministratori/create', [AmministratoriController::class, 'create'])->name('superadmin.amministratori.create');
        Route::post('/amministratori', [AmministratoriController::class, 'store'])->name('superadmin.amministratori.store');
        Route::get('/amministratori/{id}/edit', [AmministratoriController::class, 'edit'])->name('superadmin.amministratori.edit');
        Route::put('/amministratori/{id}', [AmministratoriController::class, 'update'])->name('superadmin.amministratori.update');
        Route::post('/amministratori/{id}/disable', [AmministratoriController::class, 'disable'])->name('superadmin.amministratori.disable');
        Route::post('/amministratori/{id}/enable', [AmministratoriController::class, 'enable'])->name('superadmin.amministratori.enable');
        Route::get('/amministratori/{id}/proforme/create', [AmministratoriController::class, 'createProformaPage'])->name('superadmin.amministratori.proforme.create');
        Route::post('/amministratori/{id}/proforme', [AmministratoriController::class, 'storeProforma'])->name('superadmin.amministratori.proforme.store');
        Route::get('/amministratori/{id}/proforme/{fatturazione}', [AmministratoriController::class, 'showProforma'])->name('superadmin.amministratori.proforme.show');
        Route::get('/amministratori/{id}/proforme/{fatturazione}/edit', [AmministratoriController::class, 'editProforma'])->name('superadmin.amministratori.proforme.edit');
        Route::put('/amministratori/{id}/proforme/{fatturazione}', [AmministratoriController::class, 'updateProforma'])->name('superadmin.amministratori.proforme.update');
        Route::post('/amministratori/{id}/proforme/{fatturazione}/chiudi', [AmministratoriController::class, 'closeProforma'])->name('superadmin.amministratori.proforme.close');
        Route::post('/amministratori/{id}/proforme/{fatturazione}/fatturata', [AmministratoriController::class, 'markProformaFatturata'])->name('superadmin.amministratori.proforme.mark_fatturata');
        Route::get('/amministratori/{id}/proforme/{fatturazione}/stampa', [AmministratoriController::class, 'printProforma'])->name('superadmin.amministratori.proforme.print');

        Route::get('/articoli', [ArticoliController::class, 'index'])->name('superadmin.articoli.index');
        Route::post('/articoli', [ArticoliController::class, 'store'])->name('superadmin.articoli.store');
        Route::put('/articoli/{id}', [ArticoliController::class, 'update'])->name('superadmin.articoli.update');
        Route::delete('/articoli/{id}', [ArticoliController::class, 'destroy'])->name('superadmin.articoli.destroy');

        Route::get('/proprietari', [SuperadminProprietariController::class, 'index'])->name('superadmin.proprietari.index');
        Route::get('/proprietari/create', [SuperadminProprietariController::class, 'create'])->name('superadmin.proprietari.create');
        Route::post('/proprietari', [SuperadminProprietariController::class, 'store'])->name('superadmin.proprietari.store');
        Route::get('/proprietari/{id}/edit', [SuperadminProprietariController::class, 'edit'])->name('superadmin.proprietari.edit');
        Route::put('/proprietari/{id}', [SuperadminProprietariController::class, 'update'])->name('superadmin.proprietari.update');
        Route::post('/proprietari/{id}/disable', [SuperadminProprietariController::class, 'disable'])->name('superadmin.proprietari.disable');
        Route::put('/proprietari/{id}/assegna-admin', [SuperadminProprietariController::class, 'assegnaAdmin'])->name('superadmin.proprietari.assegna_admin');
        Route::post('/proprietari/{id}/assegna-struttura', [SuperadminProprietariController::class, 'assegnaStruttura'])->name('superadmin.proprietari.assegna_struttura');
        Route::get('/proprietari/{id}/proforme/create', [SuperadminProprietariController::class, 'createProformaPage'])->name('superadmin.proprietari.proforme.create');
        Route::post('/proprietari/{id}/proforme', [SuperadminProprietariController::class, 'storeProforma'])->name('superadmin.proprietari.proforme.store');
        Route::get('/proprietari/{id}/proforme/{fatturazione}', [SuperadminProprietariController::class, 'showProforma'])->name('superadmin.proprietari.proforme.show');
        Route::get('/proprietari/{id}/proforme/{fatturazione}/edit', [SuperadminProprietariController::class, 'editProforma'])->name('superadmin.proprietari.proforme.edit');
        Route::put('/proprietari/{id}/proforme/{fatturazione}', [SuperadminProprietariController::class, 'updateProforma'])->name('superadmin.proprietari.proforme.update');
        Route::post('/proprietari/{id}/proforme/{fatturazione}/chiudi', [SuperadminProprietariController::class, 'closeProforma'])->name('superadmin.proprietari.proforme.close');
        Route::post('/proprietari/{id}/proforme/{fatturazione}/fatturata', [SuperadminProprietariController::class, 'markProformaFatturata'])->name('superadmin.proprietari.proforme.mark_fatturata');
        Route::get('/proprietari/{id}/proforme/{fatturazione}/stampa', [SuperadminProprietariController::class, 'printProforma'])->name('superadmin.proprietari.proforme.print');

        Route::get('/strutture', [SuperadminStruttureController::class, 'index'])->name('superadmin.strutture.index');
        Route::get('/strutture/create', [SuperadminStruttureController::class, 'create'])->name('superadmin.strutture.create');
        Route::post('/strutture', [SuperadminStruttureController::class, 'store'])->name('superadmin.strutture.store');
        Route::get('/strutture/{id}/edit', [SuperadminStruttureController::class, 'edit'])->name('superadmin.strutture.edit');
        Route::put('/strutture/{id}', [SuperadminStruttureController::class, 'update'])->name('superadmin.strutture.update');
        Route::put('/strutture/{id}/servizio', [SuperadminStruttureController::class, 'updateServizio'])->name('superadmin.strutture.servizio');

        Route::get('/pagamenti', [PagamentiController::class, 'index'])->name('superadmin.pagamenti.index');
        Route::post('/pagamenti/licenze', [PagamentiController::class, 'storeAssegnazione'])->name('superadmin.pagamenti.licenze.store');
        Route::put('/pagamenti/licenze/{id}', [PagamentiController::class, 'updateAssegnazione'])->name('superadmin.pagamenti.licenze.update');
        Route::delete('/pagamenti/licenze/{id}', [PagamentiController::class, 'destroyAssegnazione'])->name('superadmin.pagamenti.licenze.destroy');
        Route::get('/pagamenti/licenze/{id}/stampa', [PagamentiController::class, 'printAssegnazione'])->name('superadmin.pagamenti.licenze.print');

        Route::get('/impersonazione', [ImpersonazioneController::class, 'index'])->name('superadmin.impersonazione.index');
        Route::post('/impersona/{userId}', [ImpersonazioneController::class, 'impersona'])->name('superadmin.impersona.start');
        Route::post('/impersona/esci', [ImpersonazioneController::class, 'esci'])->name('superadmin.impersona.stop');
    });

    // QA (solo super_admin)
    Route::prefix('qa')->middleware(['ruolo:super_admin', 'qa.enabled'])->group(function () {
        Route::get('/', [QaController::class, 'index'])->name('qa.index');
        Route::get('/session', [QaController::class, 'session'])->name('qa.session');
        Route::get('/accesso', [QaController::class, 'accesso'])->name('qa.accesso');
        Route::get('/tenancy', [QaController::class, 'tenancy'])->name('qa.tenancy');
        Route::get('/demo-map', [DemoMapController::class, 'index'])->name('qa.demo-map');
    });

    // Admin area
    Route::prefix('admin')->middleware(['ruolo:admin'])->group(function () {
        Route::get('/proprietari', [AdminProprietariController::class, 'index'])->name('admin.proprietari.index');
        Route::get('/proprietari/create', [AdminProprietariController::class, 'create'])->name('admin.proprietari.create');
        Route::post('/proprietari', [AdminProprietariController::class, 'store'])->name('admin.proprietari.store');
        Route::get('/proprietari/{id}/edit', [AdminProprietariController::class, 'edit'])->name('admin.proprietari.edit');
        Route::put('/proprietari/{id}', [AdminProprietariController::class, 'update'])->name('admin.proprietari.update');
        Route::post('/proprietari/{id}/disable', [AdminProprietariController::class, 'disable'])->name('admin.proprietari.disable');
        Route::post('/proprietari/{id}/assegna-struttura', [AdminProprietariController::class, 'assegnaStruttura'])->name('admin.proprietari.assegna_struttura');
        Route::get('/proprietari/{id}/proforme/create', [AdminProprietariController::class, 'createProformaPage'])->name('admin.proprietari.proforme.create');
        Route::post('/proprietari/{id}/proforme', [AdminProprietariController::class, 'storeProforma'])->name('admin.proprietari.proforme.store');
        Route::get('/proprietari/{id}/proforme/{fatturazione}', [AdminProprietariController::class, 'showProforma'])->name('admin.proprietari.proforme.show');
        Route::get('/proprietari/{id}/proforme/{fatturazione}/edit', [AdminProprietariController::class, 'editProforma'])->name('admin.proprietari.proforme.edit');
        Route::put('/proprietari/{id}/proforme/{fatturazione}', [AdminProprietariController::class, 'updateProforma'])->name('admin.proprietari.proforme.update');
        Route::post('/proprietari/{id}/proforme/{fatturazione}/chiudi', [AdminProprietariController::class, 'closeProforma'])->name('admin.proprietari.proforme.close');
        Route::post('/proprietari/{id}/proforme/{fatturazione}/fatturata', [AdminProprietariController::class, 'markProformaFatturata'])->name('admin.proprietari.proforme.mark_fatturata');
        Route::get('/proprietari/{id}/proforme/{fatturazione}/stampa', [AdminProprietariController::class, 'printProforma'])->name('admin.proprietari.proforme.print');

        Route::get('/strutture', [AdminStruttureController::class, 'index'])->name('admin.strutture.index');
        Route::get('/strutture/create', [AdminStruttureController::class, 'create'])->name('admin.strutture.create');
        Route::post('/strutture', [AdminStruttureController::class, 'store'])->name('admin.strutture.store');
        Route::get('/strutture/{id}/edit', [AdminStruttureController::class, 'edit'])->name('admin.strutture.edit');
        Route::put('/strutture/{id}', [AdminStruttureController::class, 'update'])->name('admin.strutture.update');
        Route::put('/strutture/{id}/servizio', [AdminStruttureController::class, 'updateServizio'])->name('admin.strutture.servizio');

        Route::get('/pagamenti', [AdminPagamentiController::class, 'index'])->name('admin.pagamenti.index');
        Route::post('/pagamenti/licenze', [AdminPagamentiController::class, 'storeAssegnazione'])->name('admin.pagamenti.licenze.store');
        Route::put('/pagamenti/licenze/{id}', [AdminPagamentiController::class, 'updateAssegnazione'])->name('admin.pagamenti.licenze.update');
        Route::delete('/pagamenti/licenze/{id}', [AdminPagamentiController::class, 'destroyAssegnazione'])->name('admin.pagamenti.licenze.destroy');
        Route::get('/pagamenti/licenze/{id}/stampa', [AdminPagamentiController::class, 'printAssegnazione'])->name('admin.pagamenti.licenze.print');
    });

    // Proprietario area
    Route::prefix('proprietario')->middleware(['ruolo:proprietario'])->group(function () {
        Route::get('/strutture', [ProprietarioStruttureController::class, 'index'])->name('proprietario.strutture.index');
    });

    // Root
    Route::get('/', [HomeController::class, 'root'])->name('root');
    Route::get('/home', function () {
        return redirect()->route('root');
    })->name('home');

    //archivo txt
    Route::get('/archivos', [ArchivosController::class, 'generarArchivoHospedados'])->name('archivos');

    // clienti (slug italiano) + alias legacy
    Route::get('/clienti', [CustomerController::class, 'index'])->name('customers');
    Route::get('/clienti/liste-export', [CustomerExportController::class, 'index'])->name('customer.export.index');
    Route::get('/clienti/liste-export/csv', [CustomerExportController::class, 'exportCsv'])->name('customer.export.csv');
    Route::get('/clienti/nuovo', [CustomerController::class, 'new'])->name('newcustomer');
    Route::post('/clienti', [CustomerController::class, 'store'])->name('customer.store');
    Route::get('/clienti/{id}/modifica', [CustomerController::class, 'edit'])->name('customer.edit');
    Route::get('/clienti/{id}/stampa', [CustomerController::class, 'print'])->name('customer.print');
    Route::get('/clienti/{id}/storico', [CustomerController::class, 'storico'])->name('customer.storico');
    Route::put('/clienti/{id}', [CustomerController::class, 'update'])->name('customer.update');
    Route::delete('/clienti/{id}', [CustomerController::class, 'destroy'])->name('customer.destroy');

    Route::redirect('/customers', '/clienti');
    Route::redirect('/newcustomer', '/clienti/nuovo');
    Route::redirect('/customer_store', '/clienti');
    Route::redirect('/editcustomer/{id}', '/clienti/{id}/modifica');
    Route::redirect('/customer_update/{id}', '/clienti/{id}');
    // Rutas per AJAX
    Route::get('/provinces-by-region', [LocationController::class, 'provincesByRegion'])->name('provincesByRegion');
    Route::get('/provinces-all', [LocationController::class, 'provincesAll'])->name('provincesAll');
    Route::get('/cap-by-province', [LocationController::class, 'capByProvince'])->name('capByProvince');
    Route::get('/cities-by-province', [LocationController::class, 'citiesByProvince'])->name('citiesByProvince');

    // componenti legacy: reindirizzati al tab integrato della schedina
    Route::get('/componenti', function () {
        return redirect()->route('schedina')->with('warning', 'I componenti si gestiscono ora dentro la schedina.');
    })->name('componenti');
    Route::get('/componenti/nuovo/{schedina_id}/{customer_id}', function (int $schedina_id) {
        return redirect()->route('schedina.edit', ['id' => $schedina_id, 'active_tab' => 'schedina-step-comp']);
    })->name('newcomponenti');
    Route::post('/componenti', function () {
        $schedinaId = (int) request('schedina_id', 0);
        if ($schedinaId > 0) {
            return redirect()
                ->route('schedina.edit', ['id' => $schedinaId, 'active_tab' => 'schedina-step-comp'])
                ->with('warning', 'I componenti si salvano dal tab Componenti della schedina.');
        }

        return redirect()->route('schedina')->with('warning', 'I componenti si gestiscono ora dentro la schedina.');
    })->name('componenti.store');
    Route::get('/componenti/{id}/modifica', function (int $id) {
        $schedinaId = \App\Models\Componenti::query()->whereKey($id)->value('schedina_id');
        abort_unless($schedinaId, 404);

        return redirect()->route('schedina.edit', ['id' => $schedinaId, 'active_tab' => 'schedina-step-comp']);
    })->name('componenti.edit');
    Route::put('/componenti/{id}', function (int $id) {
        $schedinaId = \App\Models\Componenti::query()->whereKey($id)->value('schedina_id');
        abort_unless($schedinaId, 404);

        return redirect()
            ->route('schedina.edit', ['id' => $schedinaId, 'active_tab' => 'schedina-step-comp'])
            ->with('warning', 'Aggiorna i componenti dal tab Componenti della schedina.');
    })->name('componenti.update');
    Route::delete('/componenti/{id}', function (int $id) {
        $schedinaId = \App\Models\Componenti::query()->whereKey($id)->value('schedina_id');
        abort_unless($schedinaId, 404);

        return redirect()
            ->route('schedina.edit', ['id' => $schedinaId, 'active_tab' => 'schedina-step-comp'])
            ->with('warning', 'Elimina i componenti dal tab Componenti della schedina.');
    })->name('componenti.destroy');

    Route::redirect('/newcomponenti/{schedina_id}/{customer_id}', '/componenti/nuovo/{schedina_id}/{customer_id}');
    Route::redirect('/componenti_store', '/componenti');
    Route::redirect('/editcomponenti/{id}', '/componenti/{id}/modifica');
    Route::redirect('/componenti_update/{id}', '/componenti/{id}');
    // schedine (slug italiano) + alias legacy
    Route::get('/schedine', [SchedinaController::class, 'index'])->name('schedina');
    Route::get('/schedine/bozze', [SchedinaController::class, 'bozze'])->name('schedina.bozze');
    Route::get('/schedine/nuova', [SchedinaController::class, 'new'])->name('newschedina');
    Route::post('/schedine', [SchedinaController::class, 'store'])->name('schedina.store');
    Route::get('/schedine/{id}/copia', [SchedinaController::class, 'copy'])->name('schedina.copy');
    Route::get('/schedine/{id}/modifica', [SchedinaController::class, 'edit'])->name('schedina.edit');
    Route::put('/schedine/{id}', [SchedinaController::class, 'update'])->name('schedina.update');
    Route::delete('/schedine/{id}', [SchedinaController::class, 'destroy'])->name('schedina.destroy');
    Route::get('/schedine/{id}/tassa/print', [SchedinaController::class, 'printTassa'])->name('schedina.tassa.print');

    Route::redirect('/schedina', '/schedine');
    Route::redirect('/newschedina', '/schedine/nuova');
    Route::redirect('/schedina_store', '/schedine');
    Route::redirect('/editschedina/{id}', '/schedine/{id}/modifica');
    Route::redirect('/schedina_update/{id}', '/schedine/{id}');
    //gruppi (RESTful, nomi legacy)
    Route::resource('gruppi', GroupController::class)
        ->only(['index', 'store', 'update', 'destroy'])
        ->names([
            'index' => 'gruppi',
            'store' => 'gruppo.store',
            'update' => 'gruppo.update',
            'destroy' => 'gruppo.destroy',
        ]);

    //Titolo (RESTful, nomi legacy)
    Route::resource('titolo', TitleController::class)
        ->only(['index', 'store', 'update', 'destroy'])
        ->names([
            'index' => 'titolo.index',
            'store' => 'titolo.store',
            'update' => 'titolo.update',
            'destroy' => 'titolo.destroy',
        ]);

    // typeDoc
    require __DIR__ . '/typedoc.php';

    //tipovia
    Route::get('/tipovia', [TipoViaController::class, 'index'])->name('tipovia');
    Route::post('/tipovia_store', [TipoViaController::class, 'store'])->name('tipovia.store');
    Route::put('/tipovia_update/{id}', [TipoViaController::class, 'update'])->name('tipovia.update');
    Route::delete('/tipovia/{id}', [TipoViaController::class, 'destroy'])->name('tipovia.destroy');

    // Arrivi
    Route::get('/arrivi', [ArrivalsController::class, 'index'])->name('arrivals');
    Route::get('/arrivi/nuovo', [ArrivalsController::class, 'new'])->name('newarrival');
    Route::post('/arrivi', [ArrivalsController::class, 'store'])->name('arrival.store');
    Route::post('/arrivi/{id}/elimina', [ArrivalsController::class, 'destroy'])->name('arrivals.destroy');
    Route::post('/arrivi/{id}/a-schedina', [ArrivalsController::class, 'a_schedina'])->name('a_schedina');
    Route::get('/search_customers', [ArrivalsController::class, 'search'])->name('search_customers');

    //Update User Details
    Route::post('/update-profile/{id}', [HomeController::class, 'updateProfile'])->name('updateProfile');
    Route::post('/update-password/{id}', [HomeController::class, 'updatePassword'])->name('updatePassword');

    // Web Check-in
    Route::get('/web-checkin', [WebCheckinController::class, 'index'])->name('schedina.web');
    Route::get('/web-checkin/nuovo', [WebCheckinController::class, 'create'])->name('web_checkin.create');
    Route::post('/web-checkin', [WebCheckinController::class, 'store'])->name('web_checkin.store');
    Route::get('/web-checkin/{id}/modifica', [WebCheckinController::class, 'edit'])->name('web_checkin.edit');
    Route::put('/web-checkin/{id}', [WebCheckinController::class, 'update'])->name('web_checkin.update');
    Route::delete('/web-checkin/{id}', [WebCheckinController::class, 'destroy'])->name('web_checkin.destroy');
    Route::post('/web-checkin/{id}/converti', [WebCheckinController::class, 'toSchedina'])->name('web_checkin.to_schedina');
    Route::redirect('/schedine-web', '/web-checkin');

    // Questura
    Route::get('/questura', [QuesturaExportController::class, 'index'])->name('questura.index');
    Route::get('/questura/download/periodo', [QuesturaExportController::class, 'downloadPeriodo'])->name('questura.download.periodo');
    Route::get('/questura/download/schedina/{id}', [QuesturaExportController::class, 'downloadSchedina'])->name('questura.download.schedina');
    Route::get('/questura/download/storico/{id}', [QuesturaExportController::class, 'downloadStorico'])->name('questura.download.storico');
    Route::post('/questura/ws/verify', [QuesturaExportController::class, 'verifyPeriodo'])->name('questura.ws.verify');
    Route::post('/questura/ws/send', [QuesturaExportController::class, 'sendPeriodo'])->name('questura.ws.send');
    Route::get('/questura/ws/receipt/{id}', [QuesturaExportController::class, 'downloadReceipt'])->name('questura.ws.receipt');

    // Tavola A / ISTAT
    Route::get('/istat-tabella-a', [IstatTabellaAController::class, 'index'])->name('istat.tabella_a.index');
    Route::post('/istat-tabella-a/controllo', [IstatTabellaAController::class, 'saveControllo'])->name('istat.tabella_a.controllo.save');
    Route::get('/istat-tabella-a/download/xml', [IstatTabellaAController::class, 'downloadXml'])->name('istat.tabella_a.download.xml');
    Route::get('/istat-tabella-a/download/storico/{id}', [IstatTabellaAController::class, 'downloadStorico'])->name('istat.tabella_a.download.storico');
    Route::post('/istat-tabella-a/ws/verify', [IstatTabellaAController::class, 'verifyPeriodo'])->name('istat.tabella_a.ws.verify');
    Route::post('/istat-tabella-a/ws/send', [IstatTabellaAController::class, 'sendPeriodo'])->name('istat.tabella_a.ws.send');
    Route::get('/istat-tabella-a/ws/receipt/{id}', [IstatTabellaAController::class, 'downloadReceipt'])->name('istat.tabella_a.ws.receipt');

    // Presenze / statistica
    Route::get('/statistica/presenze', [PresenzeController::class, 'index'])->name('presenze.index');
    Route::get('/statistica/presenze/stampa-riepilogo', [PresenzeController::class, 'printRiepilogo'])->name('presenze.print.riepilogo');
    Route::get('/statistica/presenze/stampa-dettaglio', [PresenzeController::class, 'printDettaglio'])->name('presenze.print.dettaglio');

    // Calendario condiviso struttura
    Route::get('/calendario', [CalendarioController::class, 'index'])->name('calendario.index');
    Route::post('/calendario', [CalendarioController::class, 'store'])->name('calendario.store');
    Route::put('/calendario/{id}', [CalendarioController::class, 'update'])->name('calendario.update');
    Route::post('/calendario/{id}/stato', [CalendarioController::class, 'updateStatus'])->name('calendario.status');

    // Cestino
    Route::get('/cestino', [CestinoController::class, 'index'])->name('cestino.index');
    Route::post('/cestino/{id}/ripristina', [CestinoController::class, 'restore'])->name('cestino.restore');
    Route::delete('/cestino/{id}', [CestinoController::class, 'destroy'])->name('cestino.destroy');

    // Utenti, consegne e notifiche interne
    Route::get('/gestione-operativa', [GestioneOperativaController::class, 'index'])->name('gestione.operativa.index');
    Route::post('/gestione-operativa/profilo', [GestioneOperativaController::class, 'updateProfile'])->name('gestione.operativa.profile.update');
    Route::post('/gestione-operativa/utenti', [GestioneOperativaController::class, 'storeUtente'])->name('gestione.operativa.utenti.store');
    Route::post('/gestione-operativa/utenti/{id}', [GestioneOperativaController::class, 'updateUtente'])->name('gestione.operativa.utenti.update');
    Route::post('/gestione-operativa/utenti/{id}/password', [GestioneOperativaController::class, 'resetPassword'])->name('gestione.operativa.utenti.password');
    Route::post('/gestione-operativa/consegne', [GestioneOperativaController::class, 'storeComanda'])->name('gestione.operativa.comande.store');
    Route::post('/gestione-operativa/consegne/{id}/vista', [GestioneOperativaController::class, 'markComandaRead'])->name('gestione.operativa.comande.read');
    Route::post('/gestione-operativa/consegne/{id}/chiudi', [GestioneOperativaController::class, 'closeComanda'])->name('gestione.operativa.comande.close');
    Route::get('/notifiche', [NotificheController::class, 'index'])->name('notifiche.index');

    // Centro assistenza
    Route::get('/aiuto', [HelpCenterController::class, 'index'])->name('help.index');
    Route::get('/aiuto/generale', [HelpCenterController::class, 'general'])->name('help.general');
    Route::get('/aiuto/admin', [HelpCenterController::class, 'admin'])->name('help.admin');
    Route::get('/aiuto/stampa', [HelpCenterController::class, 'print'])->name('help.print');
    Route::get('/aiuto/moduli/{slug}', [HelpCenterController::class, 'module'])->name('help.module');
    Route::get('/aiuto/gestione/{slug}', [HelpCenterController::class, 'management'])->name('help.management');

    // Centro di supporto
    Route::get('/supporto', [SupportoController::class, 'index'])->name('supporto.index');
    Route::post('/supporto', [SupportoController::class, 'store'])->name('supporto.store');
    Route::get('/supporto/{id}', [SupportoController::class, 'show'])->name('supporto.show');
    Route::post('/supporto/{id}/rispondi', [SupportoController::class, 'reply'])->name('supporto.reply');
    Route::post('/supporto/{id}/stato', [SupportoController::class, 'updateStatus'])->name('supporto.status');
    Route::post('/supporto/{id}/assegna', [SupportoController::class, 'assign'])->name('supporto.assign');

});

Route::get('/checkin/{token}', [WebCheckinController::class, 'publicShow'])->name('web_checkin.public.show');
Route::post('/checkin/{token}', [WebCheckinController::class, 'publicStore'])->name('web_checkin.public.store');
Route::get('/checkin/{token}/completato', [WebCheckinController::class, 'publicCompleted'])->name('web_checkin.public.completed');
Route::get('/w/{token}', [WebCheckinController::class, 'publicShow'])->name('web_checkin.public.short.show');
Route::post('/w/{token}', [WebCheckinController::class, 'publicStore'])->name('web_checkin.public.short.store');
Route::get('/w/{token}/completato', [WebCheckinController::class, 'publicCompleted'])->name('web_checkin.public.short.completed');

Route::middleware(['auth'])->group(function () {
    // Catch-all
    // Non intercettare richieste ad asset pubblici (es. /build/*), altrimenti
    // Laravel salva URL errati come "intended" durante il login.
    Route::get('{any}', [HomeController::class, 'index'])
        ->where('any', '^(?!build/|storage/|images/|css/|js/|favicon\\.ico$|checkin/).*$')
        ->name('index');
});
