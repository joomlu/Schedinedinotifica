"""Trasporto dei soli cifrati: quattro flussi, unico limite globale e SHA finali."""
import concurrent.futures,hashlib,json,os,pathlib,selectors,shlex,subprocess,threading,time
import transport
P=pathlib.Path
CHUNK=16*1024*1024
REMOTE='import sys; f=open(sys.argv[1],"rb"); f.seek(int(sys.argv[2])); left=int(sys.argv[3])\nwhile left:\n b=f.read(min(left,1048576))\n if not b: raise RuntimeError("Cifrato troncato")\n sys.stdout.buffer.write(b); left-=len(b)\nsys.stdout.buffer.flush()'

def fetch_parallel(v,folder,source_root,ssh,deadline_seconds=120):
 expected=('/var/lib/schedine-release/bases/'+v['nonce']+'/cifrati')if v.get('kind')=='base'else('/var/lib/schedine-release/operations/'+v['nonce']+'/snapshot/cifrati')
 if source_root!=expected:raise RuntimeError('Sorgente fuori operazione')
 transport.verify_offer(v)
 budget=min(120,deadline_seconds)
 if budget<=0:raise RuntimeError('Budget trasporto esaurito')
 started=time.monotonic();deadline=started+budget;stop=threading.Event();lock=threading.Lock();children={};fds={};rows=[];jobs=[];failure=None
 def write_report(path,data):
  with os.fdopen(os.open(path,os.O_WRONLY|os.O_CREAT|os.O_EXCL|os.O_NOFOLLOW,0o600),'w')as f:json.dump(data,f);f.flush();os.fsync(f.fileno())
 def terminate_all():
  stop.set()
  with lock:
   for child in list(children.values()):
    if child.poll()is None:
     try:child.kill()
     except ProcessLookupError:pass
 def piece(job):
  name,index,offset,size=job;label=name+'.parte-'+str(index);began=time.monotonic();written=0;child=None;err=None
  try:
   if stop.is_set()or time.monotonic()>=deadline:raise TimeoutError('Budget globale esaurito')
   errfd=os.open(P(folder)/(label+'.stderr'),os.O_WRONLY|os.O_CREAT|os.O_EXCL|os.O_NOFOLLOW,0o600)
   with os.fdopen(errfd,'wb')as errfile:
    with lock:
     if stop.is_set():raise TimeoutError('Trasporto interrotto')
     child=subprocess.Popen(list(ssh)+[shlex.join(['python3','-c',REMOTE,source_root+'/'+name,str(offset),str(size)])],stdout=subprocess.PIPE,stderr=errfile,start_new_session=True);children[label]=child
    selector=selectors.DefaultSelector();selector.register(child.stdout,selectors.EVENT_READ)
    try:
     while True:
      remaining=deadline-time.monotonic()
      if stop.is_set()or remaining<=0:raise TimeoutError('Budget globale esaurito')
      events=selector.select(min(remaining,.25))
      if not events:continue
      block=os.read(child.stdout.fileno(),1024*1024)
      if not block:break
      if written+len(block)>size:raise RuntimeError('Parte eccedente')
      view=memoryview(block);pos=offset+written
      while view:
       n=os.pwrite(fds[name],view,pos)
       if n<=0:raise RuntimeError('Scrittura incompleta')
       view=view[n:];pos+=n
      written+=len(block)
     rc=child.wait(timeout=max(.001,deadline-time.monotonic()))
     if rc or written!=size:raise RuntimeError('Parte incompleta o comando fallito')
    finally:selector.close();child.stdout.close()
   return written
  except BaseException as exc:
   err=type(exc).__name__;terminate_all();raise
  finally:
   if child is not None:
    if child.poll()is None:
     try:child.kill()
     except ProcessLookupError:pass
    child.wait(timeout=5)
    with lock:children.pop(label,None)
   row={'file':name,'parte':index,'offset':offset,'byte_attesi':size,'byte_ricevuti':written,'failure':err,'secondi':time.monotonic()-began}
   with lock:rows.append(row)
   write_report(P(folder)/(label+'.trasporto.json'),row)
 try:
  for name,spec in v['files'].items():
   fds[name]=os.open(P(folder)/name,os.O_WRONLY|os.O_CREAT|os.O_EXCL|os.O_NOFOLLOW,0o600);os.ftruncate(fds[name],spec['byte'])
   jobs.extend((name,i,offset,min(CHUNK,spec['byte']-offset))for i,offset in enumerate(range(0,spec['byte'],CHUNK)))
  pool=concurrent.futures.ThreadPoolExecutor(max_workers=4)
  try:
   futures=[pool.submit(piece,job)for job in jobs]
   for future in concurrent.futures.as_completed(futures,timeout=max(.001,deadline-time.monotonic())):future.result()
  finally:terminate_all();pool.shutdown(wait=True,cancel_futures=True)
  for fd in fds.values():os.fsync(fd)
  for name,spec in v['files'].items():
   p=P(folder)/name
   if p.is_symlink()or p.stat().st_size!=spec['byte']:raise RuntimeError('Dimensione cifrato non conforme')
   digest=hashlib.sha256()
   with p.open('rb')as f:
    for block in iter(lambda:f.read(1024*1024),b''):
     if time.monotonic()>=deadline:raise TimeoutError('Budget verifica trasporto esaurito')
     digest.update(block)
   if digest.hexdigest()!=spec['sha256']:raise RuntimeError('Hash cifrato non conforme')
  if time.monotonic()>=deadline:raise TimeoutError('Budget globale esaurito')
 except BaseException as exc:
  failure=type(exc).__name__;terminate_all();raise
 finally:
  for fd in fds.values():os.close(fd)
  write_report(P(folder)/'trasporto-globale.json',{'esito':'PASS_TRASPORTO_CIFRATI'if failure is None else'FAIL_TRASPORTO_CIFRATI','failure':failure,'secondi':time.monotonic()-started,'budget_globale':budget,'flussi_massimi':4,'byte_ricevuti':sum(x['byte_ricevuti']for x in rows),'parti':len(rows),'parti_previste':len(jobs)})
