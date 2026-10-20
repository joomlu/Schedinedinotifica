<details class="web-altre-azioni dropdown">
<summary class="btn btn-light border btn-sm"><i class="ri-more-2-fill" aria-hidden="true"></i> Altre azioni</summary>
<div class="web-menu-azioni" role="group" aria-label="Altre azioni della richiesta">
@foreach(['Ospiti'=>['ospiti','Rivedi dati ospiti','ri-group-line'],'Accesso'=>['link','Gestisci link','ri-link'],'Attività'=>['storico','Storico','ri-history-line']] as $gruppo=>[$azione,$label,$icona])
<div class="web-menu-gruppo"><small>{{ $gruppo }}</small><a href="{{ $azione==='storico' ? route('web_checkin.comunicazioni',[$richiesta->id,'storico'=>1]) : route('web_checkin.attivita',[$richiesta->id,$azione]) }}"><i class="{{ $icona }}" aria-hidden="true"></i> {{ $label }}</a></div>
@endforeach
@if(!$richiesta->processata() && $richiesta->schedina)
<div class="web-menu-gruppo"><small>Operazioni</small><a href="{{ route('web_checkin.attivita',[$richiesta->id,'check-in']) }}"><i class="ri-hotel-bed-line" aria-hidden="true"></i> Effettua check-in</a></div>
@endif
<div class="web-menu-gruppo border-top"><a class="text-danger" href="{{ route('web_checkin.attivita',[$richiesta->id,'cestino']) }}"><i class="ri-delete-bin-line" aria-hidden="true"></i> Sposta nel cestino</a></div>
</div></details>
@once
<style>
.web-altre-azioni{position:relative}.web-altre-azioni>summary{list-style:none;display:inline-flex;gap:6px;align-items:center;min-height:44px;white-space:nowrap}.web-altre-azioni>summary::-webkit-details-marker{display:none}.web-menu-azioni{position:absolute;right:0;top:calc(100% + 6px);z-index:1050;width:260px;max-width:calc(100vw - 48px);padding:8px;background:#fff;border:1px solid #dce2e7;border-radius:10px;box-shadow:0 10px 28px #26323824}.web-menu-gruppo{padding:5px}.web-menu-gruppo small{display:block;color:#69727a;padding:2px 8px;font-size:.72rem;text-transform:uppercase;letter-spacing:.04em}.web-menu-gruppo a{display:flex;align-items:center;gap:8px;padding:10px 8px;border-radius:6px;color:#29343b}.web-menu-gruppo a:hover,.web-menu-gruppo a:focus-visible{background:#f3f5f4}.web-altre-azioni[open]>summary{background:#edf0f2;color:#29343b;border-color:#bcc6ce}.web-altre-azioni>summary:hover{background:#f0f3f5}.web-altre-azioni:focus-within>summary{outline-offset:3px}
@media(max-width:575px){.web-menu-azioni{position:static;width:100%;max-width:none;margin-top:8px;box-shadow:none}.web-altre-azioni[open]{width:100%}}
</style>
<script>document.addEventListener('click',e=>document.querySelectorAll('.web-altre-azioni[open]').forEach(d=>{if(!d.contains(e.target))d.open=false;}));document.addEventListener('keydown',e=>{if(e.key==='Escape')document.querySelectorAll('.web-altre-azioni[open]').forEach(d=>{d.open=false;d.querySelector('summary').focus();});});</script>
@endonce
