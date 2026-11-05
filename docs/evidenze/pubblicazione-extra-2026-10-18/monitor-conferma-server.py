import pathlib,json,sys,subprocess,time
P=pathlib.Path;r=P(sys.argv[1]);m=json.loads((r/'identity.json').read_bytes());sys.path.insert(0,m['server_kit']);import s0_core as c,probe,finalize_operation
p=json.loads(c.private(r/'piano-run.json').read_bytes());samples=[];start=time.monotonic();start_epoch=time.time();c.atomic(r/'monitor-conferma-inizio.json',json.dumps({'inizio_epoch':start_epoch,'C5_T0_epoch':json.loads(c.private(P(p['control_root'])/'state.json').read_bytes())['t0_epoch'],'fixture_chiusura':json.loads(c.private(r/'fixture-chiusura.json').read_bytes())['esito']}).encode())
for i in range(31):
 s=json.loads(c.private(P(p['control_root'])/'state.json').read_bytes());assert s['decision']=='CANDIDATO_ACCETTATO' and not P(p['flag']).exists();assert subprocess.run(['systemctl','is-active','--quiet','schedine-release-fpm.service']).returncode==0
 body=probe.run(p['fpm_socket'],p['private_probe'],p['probe_key'],s);assert body['version']=='12.69.3' and body['key_hash']==s['baseline_key_hash'] and body['questura'] is False and body['istat'] is False and body['mail']=='array'
 observed=finalize_operation.public_checks(p);assert all(observed.values());samples.append({'campione':i+1,'secondi':round(time.monotonic()-start,3),'HTTP_TLS_CSS_9':observed,'FPM_flags_key':'PASS'});print(json.dumps({'esito':'PASS_CAMPIONE_ONLINE','campione':i+1}),flush=True)
 if i<30:time.sleep(30)
assert time.monotonic()-start>=900
v={'esito':'PASS_MONITOR_CONFERMA','inizio_epoch':start_epoch,'secondi':time.monotonic()-start,'campioni':samples,'totale_HTTP_TLS_CSS':279,'limite':'31 campioni su almeno900 secondi del rilascio attuale; sonde in sola lettura, collaudo sintetico separato e nessun invio enti'};c.atomic(r/'monitor-conferma.json',json.dumps(v,indent=2).encode());print(json.dumps(v))
