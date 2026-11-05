import pathlib,json,sys,subprocess,os,shutil
P=pathlib.Path;r=P(sys.argv[1]);m=json.loads((r/'identity.json').read_text());sys.path.insert(0,m['server_kit']);import s0_core as c
p=json.loads(c.private(r/'piano-run.json').read_bytes());s=json.loads(c.private(P(p['control_root'])/'state.json').read_bytes());report=json.loads(c.private(P(p['control_root'])/'report.json').read_bytes());monitor=json.loads((r/'monitor-conferma.json').read_bytes());online=json.loads((r/'verifica-online-server.json').read_bytes());errors=json.loads((r/'errori-conferma.json').read_bytes())
assert report['esito']=='PASS_AGGIORNAMENTO_C5' and s['decision']=='CANDIDATO_ACCETTATO' and s['s0_verified'] and s['stopped'] and not report['ddl_started']
assert monitor['esito']=='PASS_MONITOR_CONFERMA' and monitor['secondi']>=900 and len(monitor['campioni'])==31 and monitor['totale_HTTP_TLS_CSS']==279
assert errors['totale_errori']==errors['FPM_errori_journal']==0
assert errors['totale_errori_da_T0']==3 and all(x['classificazione'].startswith('preparatore_CLI_') for x in errors['storico'])
assert errors['inizio_conferma_epoch']==monitor['inizio_epoch']
storico_monitor=json.loads(c.private(r/'monitor-online.json').read_bytes());assert storico_monitor['secondi']>=900 and storico_monitor['totale_HTTP_TLS_CSS']==279
fixture=json.loads(c.private(r/'fixture-chiusura.json').read_bytes());assert fixture['esito']=='PASS_FIXTURE_RIMOSSA_E_DATI_PREESISTENTI_IDENTICI'
sha=subprocess.check_output(['runuser','-u','tanggosoftware','--','git','-C',p['app_root'],'rev-parse','HEAD']).decode().strip();assert sha==m['sha']
print(json.dumps({'esito':'PASS_RICEVUTA_FINALE','SHA_produzione':sha,'C5':{'esito':report['esito'],'S0_secondi':report['budget']['S0_secondi'],'RUN_secondi':report['secondi_T0'],'DDL':report['ddl_started'],'decisione':s['decision']},'online_prima_collaudo_sintetico':online,'collaudo_sintetico_chiusura':fixture,'monitor':monitor,'monitor_precedente':storico_monitor,'errori':errors,'server_liberi_byte':shutil.disk_usage(p['app_root']).free}))
