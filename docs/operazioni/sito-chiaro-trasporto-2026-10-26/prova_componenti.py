from pathlib import Path
import tempfile,os,sys,json,hashlib,time,subprocess,copy,stat
import parallel_fetch as f
B=Path(__file__).resolve().parent;D=Path(tempfile.mkdtemp(prefix='componenti-cifrati-',dir=B));source=D/'sorgente';source.mkdir();events=D/'eventi';events.mkdir();os.environ['PARALLEL_FIXTURE_ROOT']=str(source);os.environ['PARALLEL_FIXTURE_EVENTS']=str(events)
for name,size in [('db.sql.enc',1024*1024),('fs.tar.gz.enc',2*f.CHUNK+512*1024),('inventory.json.enc',256*1024)]:
 with(source/name).open('wb')as p:
  for off in range(0,size,1024*1024):p.write(os.urandom(min(1024*1024,size-off)))
v={'nonce':'a'*48,'source_mode':'fixture','remaining_s0_seconds':600,'kind':'base','files':{p.name:{'byte':p.stat().st_size,'sha256':hashlib.sha256(p.read_bytes()).hexdigest()}for p in source.iterdir()}}
root='/var/lib/schedine-release/bases/'+v['nonce']+'/cifrati';stub=D/'ssh_fixture.py';stub.write_text('''import os,sys,shlex,pathlib,json,time,subprocess
r=pathlib.Path(os.environ['PARALLEL_FIXTURE_ROOT']);events=pathlib.Path(os.environ['PARALLEL_FIXTURE_EVENTS']);log=events/(str(os.getpid())+'.json');row={'pid':os.getpid(),'start':time.time_ns()};log.write_text(json.dumps(row));a=shlex.split(sys.argv[-1]);assert a[:2]==['python3','-c'];source=r/pathlib.Path(a[3]).name;mode=os.environ.get('PARALLEL_TEST_MODE','ok')
if mode=='slow':time.sleep(8)
if mode=='hash':
 p=source.open('rb');p.seek(int(a[4]));data=p.read(int(a[5]));sys.stdout.buffer.write(bytes([data[0]^1])+data[1:]);sys.stdout.buffer.flush();rc=0
else:
 if mode=='short':a[5]=str(int(a[5])-1)
 if mode=='extra':sys.stdout.buffer.write(b'X');sys.stdout.buffer.flush()
 rc=subprocess.call([sys.executable,'-c',a[2],str(source),a[4],a[5]])
row['end']=time.time_ns();log.write_text(json.dumps(row));sys.exit(rc)
''');results=[]
def run(label,mode='ok',budget=15,mutate=None,prep=None,expected_error=False,path=None):
 folder=D/label;folder.mkdir();case=copy.deepcopy(v)
 if mutate:mutate(case)
 if prep:prep(folder)
 os.environ['PARALLEL_TEST_MODE']=mode;start=time.monotonic();error=None
 try:f.fetch_parallel(case,folder,path or root,[sys.executable,str(stub)],budget)
 except Exception as e:error=type(e).__name__
 elapsed=time.monotonic()-start;assert bool(error)==expected_error,(label,error)
 if not expected_error:
  for name,spec in case['files'].items():assert hashlib.sha256((folder/name).read_bytes()).hexdigest()==spec['sha256'];assert stat.S_IMODE((folder/name).stat().st_mode)==0o600
  assert json.loads((folder/'trasporto-globale.json').read_text())['esito']=='PASS_TRASPORTO_CIFRATI'
 elif(folder/'trasporto-globale.json').exists():assert json.loads((folder/'trasporto-globale.json').read_text())['esito']=='FAIL_TRASPORTO_CIFRATI'
 results.append({'caso':label,'esito':'PASS','errore_atteso':error,'secondi':elapsed});return elapsed
run('positivo');run('limite-massimo',budget=999);assert json.loads((D/'limite-massimo/trasporto-globale.json').read_text())['budget_globale']==120
run('delta',mutate=lambda x:x.pop('kind'),path='/var/lib/schedine-release/operations/'+v['nonce']+'/snapshot/cifrati')
run('hash-alterato','hash',expected_error=True);run('parte-troncata','short',expected_error=True);run('parte-eccedente','extra',expected_error=True)
assert run('timeout-globale','slow',budget=.6,expected_error=True)<3
for p in events.iterdir():
 row=json.loads(p.read_text())
 if 'end'not in row:
  end=time.monotonic()+2
  while True:
   try:os.kill(row['pid'],0)
   except ProcessLookupError:break
   if time.monotonic()>=end:raise AssertionError('Processo rimasto attivo dopo timeout')
   time.sleep(.01)
sentinel=D/'sentinella';sentinel.write_bytes(b'ORIGINALE_DA_PRESERVARE')
run('symlink-rifiutato',prep=lambda p:(p/'db.sql.enc').symlink_to(sentinel),expected_error=True);assert sentinel.read_bytes()==b'ORIGINALE_DA_PRESERVARE'
run('file-esistente-rifiutato',prep=lambda p:(p/'db.sql.enc').write_bytes(b'ESISTENTE'),expected_error=True);assert(D/'file-esistente-rifiutato/db.sql.enc').read_bytes()==b'ESISTENTE'
run('sorgente-errata',mutate=lambda x:x.update(nonce='b'*48),expected_error=True)
run('offerta-invalida',mutate=lambda x:x.update(source_mode='non-consentita'),expected_error=True)
original_pwrite=f.os.pwrite
def no_space(*args):raise OSError(28,'Spazio sintetico esaurito')
f.os.pwrite=no_space
try:run('errore-scrittura',expected_error=True)
finally:f.os.pwrite=original_pwrite
# Concorrenza effettiva del primo caso: mai piu di quattro processi.
points=[]
for p in events.iterdir():
 row=json.loads(p.read_text())
 if 'end'in row:points.extend([(row['start'],1),(row['end'],-1)])
count=0;peak=0
for _,delta in sorted(points):count+=delta;peak=max(peak,count)
assert 1<peak<=4,peak
result={'esito':'PASS_COMPONENTI_TRASPORTO_PARALLELO','casi':results,'concorrenza_massima_osservata':peak,'limite_globale_invariato':120,'hash_finali_e_permessi':True,'processi_timeout_conclusi':True,'nessuna_rete_reale_o_dato_operativo':True,'sorgente_SHA256':hashlib.sha256((B/'parallel_fetch.py').read_bytes()).hexdigest()};(B/'prove-componenti.json').write_text(json.dumps(result,ensure_ascii=False,indent=2));print(json.dumps(result))
