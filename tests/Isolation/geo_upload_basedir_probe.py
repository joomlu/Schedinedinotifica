"""Prova FPM/Symfony senza bootstrap Laravel, solo namespace ufficiale della VM."""
import hashlib,json,os,pathlib,socket,struct,subprocess,sys,tempfile,time,shutil

assert pathlib.Path('/sys/fs/selinux/enforce').read_text().strip()=='1'
assert len(pathlib.Path('/proc/net/route').read_text().splitlines())==1
assert not pathlib.Path('/home/tanggosoftware/repos/schedinedinotifica').exists()
vendor=pathlib.Path(sys.argv[1]).resolve();image=pathlib.Path(sys.argv[2]).read_bytes()
assert len(image)==609230 and hashlib.sha256(image).hexdigest()=='8a71ffc6dbc04f879868db99e3175544face1c970fbc46dbcfad4989bd1f17d7'
root=pathlib.Path(tempfile.mkdtemp(prefix='schedine-upload-fpm-'));os.chmod(root,0o700)
process=None
def record(kind,data):
 padding=(-len(data))%8
 return struct.pack('!BBHHBB',1,kind,1,len(data),padding,0)+data+b'\0'*padding
def length(n):return bytes([n]) if n<128 else struct.pack('!I',n|0x80000000)
def request():
 boundary='CONFINE_PROVA_ISOLATA'
 body=('--'+boundary+'\r\nContent-Disposition: form-data; name="logo"; filename="Scudo_Rimini.png"\r\nContent-Type: image/png\r\n\r\n').encode()+image+('\r\n--'+boundary+'--\r\n').encode()
 params={'REQUEST_METHOD':'POST','SCRIPT_FILENAME':str(app/'public/index.php'),'SCRIPT_NAME':'/index.php','REQUEST_URI':'/geo/comuni/7510/logo','SERVER_PROTOCOL':'HTTP/1.1','SERVER_NAME':'localhost','SERVER_PORT':'80','CONTENT_TYPE':'multipart/form-data; boundary='+boundary,'CONTENT_LENGTH':str(len(body))}
 encoded=b''.join(length(len(k.encode()))+length(len(v.encode()))+k.encode()+v.encode() for k,v in params.items())
 wire=record(1,struct.pack('!HB5x',1,0))+record(4,encoded)+record(4,b'')
 for i in range(0,len(body),60000):wire+=record(5,body[i:i+60000])
 wire+=record(5,b'')
 with socket.create_connection(('127.0.0.1',19099),timeout=10) as s:
  s.sendall(wire);out=b''
  def take(n):
   b=b''
   while len(b)<n:
    t=s.recv(n-len(b));assert t;b+=t
   return b
  while True:
   _,kind,_,size,pad,_=struct.unpack('!BBHHBB',take(8));payload=take(size) if size else b''
   if pad:take(pad)
   if kind==6:out+=payload
   if kind==3:break
  return out
try:
 app=root/'app';(app/'public').mkdir(parents=True);(app/'storage/framework/upload-tmp').mkdir(parents=True,mode=0o700)
 outside=root/'outside';outside.mkdir(mode=0o700);(outside/'segreto').write_text('fixture senza dati reali')
 code='''<?php
require %s;
$request=Symfony\\Component\\HttpFoundation\\Request::createFromGlobals();
$file=$request->files->get('logo');
$size=getimagesize($file->getPathname());
$hash=hash_file('sha256',$file->getPathname());
$target=dirname(__DIR__).'/storage/logo.png';
if (!move_uploaded_file($file->getPathname(),$target)) throw new RuntimeException('Scrittura fallita');
echo json_encode(['sha256'=>$hash,'byte'=>filesize($target),'dimensioni'=>[$size[0],$size[1]],'fuori_basedir_negato'=>!@is_file(%s)]);
'''
 (app/'public/index.php').write_text(code%(json.dumps(str(vendor/'autoload.php')),json.dumps(str(outside/'segreto'))))
 results=[]
 for fixed in [False,True]:
  tmp=app/'storage/framework/upload-tmp' if fixed else outside
  conf=root/'pool.conf';error=root/('corretto.log' if fixed else 'difetto.log')
  conf.write_text('[global]\npid='+str(root/'fpm.pid')+'\nerror_log='+str(root/'master.log')+'\ndaemonize=no\n[isolato]\nlisten=127.0.0.1:19099\npm=static\npm.max_children=1\nphp_admin_flag[display_errors]=off\nphp_admin_flag[log_errors]=on\nphp_admin_flag[zend.exception_ignore_args]=on\nphp_admin_value[error_log]='+str(error)+'\nphp_admin_value[open_basedir]='+str(app)+':'+str(vendor)+':/proc/self\nphp_admin_value[upload_tmp_dir]='+str(tmp)+'\n')
  subprocess.run(['/usr/sbin/php-fpm','-t','-y',str(conf)],check=True,capture_output=True)
  process=subprocess.Popen(['/usr/sbin/php-fpm','-F','-y',str(conf)],stdout=subprocess.DEVNULL,stderr=subprocess.DEVNULL)
  for _ in range(100):
   if process.poll() is not None:raise RuntimeError('FPM isolato terminato')
   try:
    with socket.create_connection(('127.0.0.1',19099),timeout=.1):break
   except OSError:time.sleep(.05)
  response=request()
  process.terminate();process.wait(timeout=10);process=None
  if not fixed:
   log=error.read_text();assert b'Status: 500' in response and 'open_basedir restriction' in log and 'FileNotFoundException' in log and 'File.php:36' in log
   assert not (app/'storage/logo.png').exists()
   results.append({'prova':'difetto originale','HTTP':500,'eccezione':'FileNotFoundException','prima_della_scrittura':True})
  else:
   body=json.loads(response.split(b'\r\n\r\n',1)[1]);assert body['sha256']==hashlib.sha256(image).hexdigest() and body['byte']==609230 and body['dimensioni']==[960,1251] and body['fuori_basedir_negato']
   assert not error.exists() or not error.read_text().strip()
   assert list(tmp.iterdir())==[]
   results.append({'prova':'directory temporanea privata','HTTP':200,**body,'temporanei_rimossi':True})
 print(json.dumps({'esito':'PASS','FPM':subprocess.check_output(['/usr/sbin/php-fpm','-v'],text=True).splitlines()[0],'nessun_bootstrap_Laravel_o_DB':True,'namespace_senza_rete_esterna':True,'prove':results}))
finally:
 if process and process.poll() is None:process.terminate();process.wait(timeout=10)
 shutil.rmtree(root)
