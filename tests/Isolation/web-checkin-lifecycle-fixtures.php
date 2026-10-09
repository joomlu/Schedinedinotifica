<?php
require __DIR__.'/web-checkin-camera-fixtures.php';
$base=json_decode(file_get_contents(public_path('baseline-final-ids.json')), true);
$r=\App\Models\WebCheckinRichiesta::where('token',$base['full'])->firstOrFail();
$r->update(['arrivo'=>now()->toDateString(),'partenza'=>now()->addDays(3)->toDateString()]);
$r->schedina()->withoutGlobalScopes()->update(['arrive'=>now()->toDateString(),'departure'=>now()->addDays(3)->toDateString()]);
app(\App\Services\WebCheckinLink::class)->issue($r);
$ids=['id'=>$r->id,'full'=>$r->token,'short'=>$r->short_token];
foreach (['converted','expired','revoked','legacy','wifi2','wifi3','posts','reads','reschedule','operational','trash'] as $kind) {
 $copy=$r->replicate();$copy->codice='LC'.strtoupper($kind);$copy->token=\Illuminate\Support\Str::random(64);$copy->short_token=\Illuminate\Support\Str::random(32);$copy->save();
 if($kind==='converted'){$copy->update(['stato'=>'convertito','convertito_at'=>now()]);}
 if($kind==='expired'){$copy->forceFill(['link_expires_at'=>now()->subSecond()])->save();}
 if($kind==='revoked'){app(\App\Services\WebCheckinLink::class)->revoke($copy);}
 if($kind==='legacy'){$copy->forceFill(['token'=>str_repeat('l',80),'short_token'=>null,'link_expires_at'=>null,'link_issued_at'=>null])->save();}
 if(in_array($kind,['wifi2','wifi3','posts','reads'])){$copy->update(['stato'=>'convertito']);}
 if($kind==='wifi3'){$s=$r->struttura->replicate();$s->nome_struttura='Wi-Fi altra struttura';$s->cir=null;$s->save();$p=\App\Models\Schedina::withoutGlobalScopes()->findOrFail($r->schedina_id)->replicate();$p->struttura_id=$s->id;$p->save();$copy->update(['struttura_id'=>$s->id,'schedina_id'=>$p->id]);}
 if(in_array($kind,['reschedule','operational','trash'])){$parent=\App\Models\Schedina::withoutGlobalScopes()->findOrFail($r->schedina_id)->replicate();$parent->name='OSPITE-'.$kind;$parent->surname='Sintetico';$parent->cant_people=1;$parent->save();$copy->update(['schedina_id'=>$parent->id,'quantita_persone'=>1,'numero_prenotazione'=>'CONTRATTO-'.$kind]);}
 $ids[$kind]=['id'=>$copy->id,'full'=>$copy->token,'short'=>$copy->short_token,'parent'=>$copy->schedina_id];
}
file_put_contents(public_path('web-lifecycle-ids.json'),json_encode($ids));

$ids['dates']=['arrival'=>now()->addDays(2)->toDateString(),'departure'=>now()->addDays(5)->toDateString(),'expiry'=>now()->addDays(4)->startOfDay()->toJSON()];
file_put_contents(public_path('web-lifecycle-ids.json'),json_encode($ids));
$route=<<<'ROUTE'

\Illuminate\Support\Facades\Route::middleware(['web','auth'])->get('/audit-token/state/{kind}',function(string $kind){
    abort_unless(in_array($kind,['reschedule','operational','trash']),404);
    $ids=json_decode(file_get_contents(public_path('web-lifecycle-ids.json')),true);
    $owner=\App\Models\WebCheckinRichiesta::findOrFail($ids['id']);
    abort_unless(auth()->user()->struttura_id===$owner->struttura_id,403);
    $r=\App\Models\WebCheckinRichiesta::where('numero_prenotazione','CONTRATTO-'.$kind)->first();
    $item=\App\Models\CestinoItem::where('entity_class',\App\Models\WebCheckinRichiesta::class)->orderByDesc('id')->first();
    return response()->json(['request'=>$r?->only(['id','token','short_token','arrivo','link_expires_at','link_revoked_at','stato']),'parent'=>$r?->schedina?->only(['id','name','circuito']),'item'=>$item?->id]);
});
ROUTE;
$routes=file_get_contents(base_path('routes/web.php'));
$point="Route::middleware(['auth'])->group(function () {\n    // Catch-all";
if(substr_count($routes,$point)!==1){throw new \RuntimeException('Punto di osservabilità non univoco');}
file_put_contents(base_path('routes/web.php'),str_replace($point,$route."\n".$point,$routes));
