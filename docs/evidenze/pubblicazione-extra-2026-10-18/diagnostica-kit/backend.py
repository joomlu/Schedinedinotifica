"""Backend sorgente concreto, guidato da mappa/approvazione root sigillate.
Mai inferire autorizzazione dal deposito o da un risultato sintetico.
"""
import hashlib,json,os,pathlib,selectors,sys,time,threading
import s0_core as c
import s0_native as n
import ingress,probe,transport
from drain import wait
P=pathlib.Path

class SourceBackend:
 def __init__(self,plan,control):
  self.plan=plan;self.control=control;self.volume_uuid=plan['volume_uuid']
  self.key=c.private(plan['transport_key']);self.auth_key=self.key.read_bytes().strip()
  base=c.canonical(plan['operations_root'])
  if base.stat().st_uid!=0 or base.stat().st_mode&0o077:raise RuntimeError('Deposito operazioni non privato')
  self.operation=base/control.state['nonce']
  if not self.operation.exists():self.operation.mkdir(mode=0o700)
  self.out=self.operation/'snapshot';self.lease=None
  self.seals=json.loads(c.private(self.out/'seals.json').read_bytes()) if (self.out/'seals.json').exists() else None
  self.decoded=None;self.monitor=None
 def monitor_error(self,reason):
  c.atomic(self.operation/'monitor-ingress-stop.json',json.dumps({'errore':reason,'nonce':self.control.state['nonce'],'epoch':time.time()}).encode())
 def barrier(self):
  if threading.current_thread() is threading.main_thread():self.control.elapsed()
  if not self.control.flag.exists() or c.digest(self.control.flag)!=self.control.state['barrier_hash']:raise RuntimeError('Barriera assente/alterata')
  if not ingress.socket_quiet(self.plan['legacy_fpm_socket']):raise RuntimeError('Socket FPM precedente non quieto: STOP')
  return all(__import__('subprocess').run(args,capture_output=True,timeout=3).stdout.strip()==b'503 0' for args in self.plan['barrier_checks'])
 def preflight(self,deadline):
  n.context(self.plan,mutation=False)
  if not self.barrier():raise RuntimeError('Barriera destinatario non attestata')
  from fcgi import request
  status=request(self.plan['status_socket'],'/schedine-status',{'SCRIPT_NAME':'/schedine-status','REQUEST_URI':'/schedine-status?json','QUERY_STRING':'json'})
  if status.get('active processes')!=0:raise RuntimeError('Richieste FPM pilota ancora attive: STOP')
  ingress.process_gate(self.plan['app_root'],self.plan['app_uid'],{os.getpid()})
  wait(n.Database(self.plan),self.barrier,min(deadline,time.monotonic()+20))
  return probe.run(self.plan['fpm_socket'],self.plan['private_probe'],self.plan['probe_key'],self.control.state)
 def capture(self,deadline):
  db=n.Database(self.plan);wait(db,self.barrier,min(deadline,time.monotonic()+20))
  self.lease=ingress.TableLease(db,deadline).__enter__()
  try:
   ingress.guard(db,self.barrier,self.lease,self.plan['app_root'],self.plan['app_uid'],{os.getpid()})
   from monitor import Monitor
   def check_ingress():
    # Il READ lock impedisce scritture target; non confondere il dump posseduto
    # e la sua transazione consistente con un ingresso estraneo.
    if not self.barrier():raise RuntimeError('Barriera persa')
    self.lease.attest()
    ingress.process_gate(self.plan['app_root'],self.plan['app_uid'],{os.getpid()})
   self.monitor=Monitor(check_ingress,error_sink=self.monitor_error).__enter__()
   self.out.mkdir(mode=0o700)
   self.seals=n.capture(self.plan,self.out,self.key)
   ingress.guard(db,self.barrier,self.lease,self.plan['app_root'],self.plan['app_uid'],{os.getpid()})
   return self.seals
  except BaseException:self.close();raise
 def transfer(self,offer,deadline):
  # Emissione metadati soli, non chiave/inventario in chiaro.
  message={'evento':'CIFRATI_PRONTI','offerta':{'data':offer,'mac':transport.sign(offer,self.auth_key)}}
  print(json.dumps(message),flush=True)
  reader=selectors.DefaultSelector();reader.register(sys.stdin,selectors.EVENT_READ)
  try:
   while time.monotonic()<deadline-30:
    if reader.select(.1):
     line=sys.stdin.buffer.readline(1024*1024)
     if not line.endswith(b'\n'):raise RuntimeError('Ricevuta assente/eccessiva')
     return json.loads(line)
    self.control.elapsed();self.lease.attest()
    if not ingress.socket_quiet(self.plan['legacy_fpm_socket']):raise RuntimeError('Nuovo ingresso nel pool precedente: STOP')
   raise RuntimeError('Trasporto senza margine verifica: STOP')
  finally:reader.close()
 def verify(self,offer,receipt,deadline):
  d=receipt['data'];proof=d['restore_proof']
  required={'independent','no_network','db','files','metadata','key','fpm','tls','flags'}
  if not all(proof.get(k) is True for k in required):raise RuntimeError('Recupero indipendente incompleto')
  if proof.get('schema_sha256')!=n.Database(self.plan).schema()['sha256']:raise RuntimeError('Schema ricevente differente')
  if proof.get('db_sha256')!=self.seals['db.sql']['plain_sha256'] or proof.get('inventory_sha256')!=self.seals['inventory.json']['plain_sha256']:raise RuntimeError('Contenuto ricevente differente')
  ingress.guard(n.Database(self.plan),self.barrier,self.lease,self.plan['app_root'],self.plan['app_uid'],{os.getpid()})
  self.close()
  checks={k:True for k in ['db','files','metadata','key','fpm','tls','barrier','zero_queries','offhost']}
  return {'nonce':self.control.state['nonce'],'sha':c.SHA,'checks':checks,'cipher_hashes':{k:s['cipher_sha256'] for k,s in self.seals.items()}}
 def has_recovery(self):
  # Una fase backup non ha mutato app/DB: non fare DROP/restore automaticamente.
  return bool(self.control.state.get('ddl_started') or self.control.state.get('app_changed')) and self.seals is not None
 def recover(self,deadline):
  self.close()
  if self.plan.get('restore_authorized') is not True:raise RuntimeError('Recupero produttivo non autorizzato: mantenere STOP')
  decoded=self.operation/('decoded-'+os.urandom(8).hex());decoded.mkdir(mode=0o700)
  for name,s in self.seals.items():c.decrypt(self.out/'cifrati'/(name+'.enc'),decoded/name,self.key,s)
  manifest=json.loads((decoded/'inventory.json').read_bytes());extracted=decoded/'estratto'
  c.extract(decoded/'fs.tar.gz',manifest,extracted);c.verify_extracted(extracted,manifest)
  n.recover_files(self.plan,manifest,extracted,self.operation/('fallito-'+os.urandom(8).hex()))
  n.Database(self.plan).restore(decoded/'db.sql','release-recovery')
  return self.accept(deadline)
 def accept(self,deadline):
  self.close()
  result=probe.run(self.plan['fpm_socket'],self.plan['private_probe'],self.plan['probe_key'],self.control.state)
  return {'db':result['tables']==self.plan['database']['tables'],'files':c.inventory(self.plan)['records']==json.loads((self.out/'inventory.json').read_bytes())['records'],
          # Gate non ancora implementato: non attestare configurazione per assunzione.
          'config':False,'key':result['key'],'fpm':True,'tls':self.barrier(),'egress':result['questura'] is False and result['istat'] is False,'zero_queries':n.Database(self.plan).zero_queries()}
 def close(self):
  try:
   if self.monitor:
    monitor=self.monitor;self.monitor=None;monitor.__exit__(None,None,None)
  finally:
   if self.lease:
    lease=self.lease;self.lease=None;lease.close()

def approve(plan_path,approval_path,operation,manifest_hash):
 plan_bytes=c.private(plan_path).read_bytes();plan=json.loads(plan_bytes);approval=json.loads(c.private(approval_path).read_bytes())
 if approval.get('plan_sha256')!=hashlib.sha256(plan_bytes).hexdigest() or approval.get('manifest_sha256')!=manifest_hash or approval.get('sha')!=c.SHA:raise RuntimeError('Approvazione non riconciliata')
 if operation not in approval.get('operations',[]) or time.time()>approval.get('expires_epoch',0):raise RuntimeError('Operazione non autorizzata o scaduta')
 if plan['mode']=='production':
  if plan['app_root']!='/home/tanggosoftware/repos/schedinedinotifica' or plan['control_root']!='/var/lib/schedine-release/control' or plan['flag']!='/var/lib/schedine-release/barrier/closed':raise RuntimeError('Destinatario fuori mappa')
 # Nessuna approvazione viene generata da questa CLI.
 return plan
