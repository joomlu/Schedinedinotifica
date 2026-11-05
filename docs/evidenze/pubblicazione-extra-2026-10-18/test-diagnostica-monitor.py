import sys,pathlib,unittest,time,signal,tempfile,json,stat,types
sys.path.insert(0,sys.argv.pop(1));from monitor import Monitor
import backend
class Diagnostica(unittest.TestCase):
 def test_successo_senza_diagnostici(self):
  out=[]
  with Monitor(lambda:None,error_sink=out.append) as m:time.sleep(.22)
  self.assertGreaterEqual(m.samples,1);self.assertEqual(out,[]);self.assertEqual(m.interval,.1)
 def exercise(self,sink):
  calls=0
  def check():
   nonlocal calls;calls+=1
   if calls>1:raise RuntimeError('SENTINELLA_GATE')
  m=Monitor(check,error_sink=sink)
  with self.assertRaises(RuntimeError):
   with m:time.sleep(.5)
  self.assertTrue(m.failed.is_set());self.assertEqual(m.reason,'RuntimeError: SENTINELLA_GATE')
 def test_STOP_e_motivo_originale(self):
  out=[];self.exercise(out.append);self.assertEqual(out,['RuntimeError: SENTINELLA_GATE'])
 def test_errore_registrazione_non_impedisce_STOP(self):
  def fail(_):raise OSError('SENTINELLA_SCRITTURA')
  self.exercise(fail)
 def test_STOP_senza_callback_compatibile(self):self.exercise(None)
 def test_fallimento_iniziale_non_avvia_thread(self):
  previous=signal.getsignal(signal.SIGUSR1)
  def fail():raise ValueError('SENTINELLA_INIZIALE')
  with self.assertRaises(ValueError):Monitor(fail,error_sink=lambda _:None).__enter__()
  self.assertEqual(signal.getsignal(signal.SIGUSR1),previous)
 def test_backend_diagnostico_privato_nonce_corretto(self):
  with tempfile.TemporaryDirectory(prefix='schedine-test-diagnostica-')as d:
   p=pathlib.Path(d);p.chmod(0o700);k=p/'key';k.write_text('SINTETICA_NON_SEGRETA');k.chmod(0o600);ops=p/'ops';ops.mkdir(mode=0o700)
   ctl=types.SimpleNamespace(state={'nonce':'f'*48});b=backend.SourceBackend({'mode':'fixture','volume_uuid':'sintetico','transport_key':str(k),'operations_root':str(ops)},ctl);b.monitor_error('RuntimeError: SENTINELLA_GATE');f=b.operation/'monitor-ingress-stop.json';v=json.loads(f.read_bytes());self.assertEqual(v['nonce'],'f'*48);self.assertEqual(v['errore'],'RuntimeError: SENTINELLA_GATE');self.assertEqual(stat.S_IMODE(f.stat().st_mode),0o600);self.assertEqual(f.stat().st_uid,0)
unittest.main()
