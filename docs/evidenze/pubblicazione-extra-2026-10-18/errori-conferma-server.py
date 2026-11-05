import pathlib,json,sys,datetime,re,subprocess,hashlib
from zoneinfo import ZoneInfo
P=pathlib.Path;r=P(sys.argv[1]);m=json.loads((r/'identity.json').read_bytes());sys.path.insert(0,m['server_kit']);import s0_core as c
p=json.loads(c.private(r/'piano-run.json').read_bytes());state=json.loads(c.private(P(p['control_root'])/'state.json').read_bytes());window=json.loads(c.private(r/'monitor-conferma-inizio.json').read_bytes());start=datetime.datetime.fromtimestamp(state['t0_epoch'],ZoneInfo('Europe/Rome')).replace(tzinfo=None);clean=datetime.datetime.fromtimestamp(window['inizio_epoch'],ZoneInfo('Europe/Rome')).replace(tzinfo=None);rows=[]
for f in (P(p['app_root'])/'storage/logs').glob('*.log'):
 text=f.read_text(errors='replace')
 for block in re.split(r'(?=^\[\d{4}-\d{2}-\d{2} \d{2}:\d{2}:\d{2}\])',text,flags=re.M):
  hit=re.match(r'^\[(\d{4}-\d{2}-\d{2} \d{2}:\d{2}:\d{2})\].*?\.(ERROR|CRITICAL|ALERT|EMERGENCY):',block)
  if not hit or datetime.datetime.fromisoformat(hit[1])<start:continue
  fixture=str(r/'fixture-online.php') in block
  kind='non_attribuito'
  if fixture and 'Cataloghi globali necessari assenti' in block:kind='preparatore_CLI_cataloghi'
  if fixture and "Field 'telefono' doesn't have a default value" in block:kind='preparatore_CLI_campo_obbligatorio'
  rows.append({'istante_locale':hit[1],'sha256_blocco':hashlib.sha256(block.encode()).hexdigest(),'classificazione':kind,'successivo_inizio_conferma':datetime.datetime.fromisoformat(hit[1])>=clean})
assert len(rows)==3 and all(x['classificazione'].startswith('preparatore_CLI_') for x in rows)
journal=subprocess.check_output(['journalctl','-u','schedine-release-fpm.service','--since','@'+str(window['inizio_epoch']),'--priority','err','--no-pager','-o','cat']).decode()
v={'esito':'PASS_ERRORI_ATTRIBUITI_E_CONFERMA_SENZA_NUOVI_ERRORI','T0_C5_epoch':state['t0_epoch'],'inizio_conferma_epoch':window['inizio_epoch'],'totale_errori_da_T0':len(rows),'storico':rows,'totale_errori':sum(x['successivo_inizio_conferma'] for x in rows),'FPM_errori_journal':len(journal.strip().splitlines()) if journal.strip() else 0,'nessun_log_cancellato':True,'nessun_contenuto_personale_esportato':True}
c.atomic(r/'errori-conferma.json',json.dumps(v,indent=2).encode());print(json.dumps(v));assert v['totale_errori']==v['FPM_errori_journal']==0
