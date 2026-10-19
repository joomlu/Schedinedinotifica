<?php
// Solo checkout effimero attestato. Nessuna impostazione di posta applicata al progetto o alla produzione.
require __DIR__.'/seed-web-checkin-html.php';
if (!str_starts_with(base_path(), '/tmp/schedine-test-')) throw new RuntimeException('Solo runtime isolato');
$code = <<<'PHP'

// Trasporto di collaudo presente solo nella copia effimera, senza socket SMTP.
if (!str_starts_with(base_path(), '/tmp/schedine-test-')) throw new RuntimeException('Fixture fuori isolamento');
config(['web_checkin.email_abilitata'=>true,'web_checkin.mailer'=>'web_preview_test','mail.mailers.web_preview_test'=>['transport'=>'smtp','host'=>'127.0.0.1','from'=>['address'=>'reception@example.invalid','name'=>'Reception sintetica']]]);
\Illuminate\Support\Facades\Mail::extend('smtp',fn()=>new class extends \Illuminate\Mail\Transport\ArrayTransport {
 public function send(\Symfony\Component\Mime\RawMessage $message, ?\Symfony\Component\Mailer\Envelope $envelope=null): ?\Symfony\Component\Mailer\SentMessage {
  $sent=parent::send($message,$envelope);file_put_contents(storage_path('framework/email-intercettate.jsonl'),json_encode(['html'=>$message->getHtmlBody(),'oggetto'=>$message->getSubject(),'a'=>array_map(fn($a)=>$a->getAddress(),$message->getTo()),'allegati'=>count($message->getAttachments())])."\n",FILE_APPEND|LOCK_EX);return $sent;
 }
});
\Illuminate\Support\Facades\Route::get('/email-intercettate-fixture',function(){return response()->json(['messaggi'=>array_map(fn($x)=>json_decode($x,true),is_file(storage_path('framework/email-intercettate.jsonl'))?file(storage_path('framework/email-intercettate.jsonl'),FILE_IGNORE_NEW_LINES):[])]);})->middleware('auth');
PHP;
$routes=file_get_contents(base_path('routes/web.php'));
if (!str_starts_with($routes,'<?php')) throw new RuntimeException('Route non riconosciute');
file_put_contents(base_path('routes/web.php'),'<?php'.$code.substr($routes,5));
