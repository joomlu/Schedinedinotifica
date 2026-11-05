"""Monitor continuo dei gate; segnala STOP, non termina utilizzatori estranei."""
import os,signal,threading,time

class Monitor:
 def __init__(self,check,interval=.1,error_sink=None):
  self.error_sink=error_sink
  self.check=check;self.interval=interval;self.done=threading.Event();self.failed=threading.Event();self.samples=0
 def __enter__(self):
  if threading.current_thread() is not threading.main_thread():raise RuntimeError('Monitor fuori supervisore')
  self.previous=signal.signal(signal.SIGUSR1,self.interrupt)
  try:self.check()
  except BaseException:
   signal.signal(signal.SIGUSR1,self.previous);raise
  self.thread=threading.Thread(target=self.watch,name='schedine-monitor',daemon=True);self.thread.start();return self
 def interrupt(self,*_):raise RuntimeError('Ingresso/gate non conforme durante operazione: STOP')
 def watch(self):
  while not self.done.wait(self.interval):
   try:self.check();self.samples+=1
   except BaseException as error:
    self.reason=type(error).__name__+": "+str(error)
    self.failed.set()
    if not self.done.is_set():os.kill(os.getpid(),signal.SIGUSR1)
    try:
     if self.error_sink:self.error_sink(self.reason)
    except BaseException:pass
    return
 def __exit__(self,*exc):
  mask=signal.pthread_sigmask(signal.SIG_BLOCK,{signal.SIGUSR1})
  try:
   self.done.set();self.thread.join(timeout=25)
   if signal.SIGUSR1 in signal.sigpending():signal.sigwait({signal.SIGUSR1})
   signal.signal(signal.SIGUSR1,self.previous)
  finally:signal.pthread_sigmask(signal.SIG_SETMASK,mask)
  if self.thread.is_alive():raise RuntimeError('Monitor non arrestato: STOP')
  if self.failed.is_set() and exc[0] is None:raise RuntimeError('Monitor ha rilevato un errore: STOP')
