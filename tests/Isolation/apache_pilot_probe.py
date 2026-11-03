"""Regressioni Apache/FPM: eseguire solo nel namespace privato predisposto."""
import base64, http.server, json, os, pathlib, subprocess, threading, time, urllib.request, urllib.error
w=pathlib.Path('/work'); report={'esito':'FAIL','controlli':[]}; processes=[]
def check(name, condition):
 report['controlli'].append({'prova':name,'esito':'PASS' if condition else 'FAIL'})
 if not condition:raise RuntimeError(name)
def request(path,method='GET',headers=None):
 req=urllib.request.Request('http://127.0.0.1:8080'+path,data=b'' if method=='POST' else None,method=method,headers=headers or {})
 try:
  with urllib.request.urlopen(req,timeout=5) as r:return r.status,r.read()
 except urllib.error.HTTPError as e:return e.code,e.read()
class Backend(http.server.BaseHTTPRequestHandler):
 count=0
 def do_GET(self):
  Backend.count+=1;self.send_response(200);self.end_headers();self.wfile.write(b'BACKEND_PRIVATO')
 def log_message(self,*args):pass
try:
 check('namespace_senza_route',len(pathlib.Path('/proc/net/route').read_text().splitlines())==1)
 check('produzione_non_montata',not pathlib.Path('/home/tanggosoftware/repos/schedinedinotifica').exists())
 site=w/'site';site.mkdir();(site/'images').mkdir();(site/'uploads').mkdir();(w/'data-public').mkdir();(site/'storage').symlink_to(w/'data-public',target_is_directory=True)
 png=base64.b64decode('iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAQAAAC1HAwCAAAAC0lEQVR42mP8/x8AAwMCAO+jRZkAAAAASUVORK5CYII=')
 (site/'index.php').write_text('<?php echo "FRONT_OK:".$_SERVER["REMOTE_ADDR"]; ?>')
 (site/'.htaccess').write_bytes((w/'htaccess-originale').read_bytes())
 for d in [site/'images',site/'uploads',w/'data-public']:
  (d/'probe.php').write_bytes(png+b'<?php echo "EXECUTED"; ?>');(d/'logo.png').write_bytes(png);(d/'probe.PHP').write_text('<?php echo "EXECUTED"; ?>');(d/'probe.php.jpg').write_text('<?php echo "EXECUTED"; ?>')
 legacy=json.loads((w/'legacy-assets.json').read_text())
 for p in legacy+['build/assets/app-fixture.js']:
  f=site/p;f.parent.mkdir(parents=True,exist_ok=True);f.write_text('/* ASSET_SINTETICO */')
 (w/'pool.conf').write_text('[global]\npid=/work/fpm.pid\nerror_log=/work/fpm.log\ndaemonize=no\n[privato]\nlisten=/work/fpm.sock\nlisten.mode=0600\npm=static\npm.max_children=2\nclear_env=yes\nsecurity.limit_extensions=.php\n')
 fpm=subprocess.Popen(['/opt/remi/php83/root/usr/sbin/php-fpm','-F','-y','/work/pool.conf'],stdout=subprocess.DEVNULL,stderr=subprocess.DEVNULL);processes.append(fpm)
 for _ in range(100):
  if (w/'fpm.sock').exists():break
  time.sleep(.1)
 backend=http.server.ThreadingHTTPServer(('127.0.0.1',9001),Backend);threading.Thread(target=backend.serve_forever,daemon=True).start()
 modules=['mpm_event','unixd','authz_core','authz_host','dir','mime','alias','rewrite','negotiation','proxy','proxy_http','proxy_fcgi','remoteip']
 base='ServerRoot /work\nServerName pilot.test\nListen 127.0.0.1:8080\nPidFile /work/httpd.pid\nErrorLog /work/httpd-error.log\n'+'\n'.join('LoadModule '+n+'_module /usr/lib64/httpd/modules/mod_'+n+'.so' for n in modules)+'\nRewriteEngine On\nRewriteRule ^/swrapper/(.*) http://127.0.0.1:9001/inherited/$1 [P,L]\nTypesConfig /work/mime.types\nRemoteIPHeader CF-Connecting-IP\nRemoteIPTrustedProxy 103.21.244.0/22\n<Directory />\nRequire all denied\n</Directory>\n'
 (w/'mime.types').write_text('image/png png\ntext/javascript js\n')
 baseline='<VirtualHost 127.0.0.1:8080>\nServerName pilot.test\nServerAlias alias.pilot.test\nDocumentRoot /work/site\n<Directory /work/site>\nRequire all granted\nAllowOverride All\nOptions FollowSymLinks\n<FilesMatch \\.php$>\nSetHandler "proxy:unix:/work/fpm.sock|fcgi://localhost"\n</FilesMatch>\n</Directory>\n<Directory /work/data-public>\nRequire all granted\nAllowOverride All\n<FilesMatch \\.php$>\nSetHandler "proxy:unix:/work/fpm.sock|fcgi://localhost"\n</FilesMatch>\n</Directory>\nRewriteEngine On\nRewriteOptions InheritBefore\nRewriteRule ^/phpdbadmin/(.*) http://127.0.0.1:9001/sqladmin/$1 [P,L]\nPOLICY\n</VirtualHost>\n'
 policy=(w/'apache-pilot.conf').read_text().replace('/home/tanggosoftware/repos/schedinedinotifica/public','/work/site').replace('/home/tanggosoftware/repos/schedinedinotifica/storage/app/public','/work/data-public')
 for phase,extra in [('prima',''),('dopo',policy)]:
  conf=w/(phase+'.conf');conf.write_text(base+baseline.replace('POLICY',extra))
  p=subprocess.run(['httpd','-t','-f',str(conf)],capture_output=True);(w/(phase+'-sintassi.log')).write_bytes(p.stderr);check(phase+'_sintassi',p.returncode==0)
  apache=subprocess.Popen(['httpd','-DFOREGROUND','-f',str(conf)],stdout=subprocess.DEVNULL,stderr=subprocess.DEVNULL);processes.append(apache)
  for _ in range(100):
   try:request('/login');break
   except (OSError,urllib.error.URLError):time.sleep(.05)
  if phase=='prima':
   check('rischio_php_images_riprodotto',request('/images/probe.php')[1].endswith(b'EXECUTED'))
   check('rischio_php_storage_riprodotto',request('/storage/probe.php')[1].endswith(b'EXECUTED'))
   check('proxy_reale_sintetico_prima',request('/phpdbadmin/prova')[1]==b'BACKEND_PRIVATO')
   check('proxy_ereditato_prima',request('/swrapper/prova')[1]==b'BACKEND_PRIVATO')
   check('ckeditor_prima_pubblico',request('/build/libs/@ckeditor/ckeditor5-build-classic/build/ckeditor.js')[0]==200)
  else:
   for root in ['register','contatti/richiesta','phpdbadmin','sinstaller','webmail','roundwebclient','webftpmanager','postgresqldbadmin','directproxyupload','swrapper','spanel']:
    for suffix in ['', '/prova']:
     for method in ['GET','POST']:
      count=Backend.count;check('diniego_'+method+'_'+root+suffix,request('/'+root+suffix,method)[0]==403 and Backend.count==count)
   for path in ['/register?x=1','/%72egister','//register','/phpdbadmin%2fprova']:
    check('variante_'+path,request(path)[0] in [400,403,404])
   for root in ['images','storage','uploads']:
    for file in ['probe.php','probe.PHP','probe.php.jpg','probe.php/path']:
     code,body=request('/'+root+'/'+file);check('upload_'+root+'/'+file,code in [403,404] and b'<?php' not in body and not body.endswith(b'EXECUTED'))
    check('immagine_'+root,request('/'+root+'/logo.png')==(200,png))
   for file in ['ckeditor.js','ckeditor.js.map']:
    check('ckeditor410_'+file,request('/build/libs/@ckeditor/ckeditor5-build-classic/build/'+file)[0]==410)
   for path in legacy:
    check('ckeditor410_inventario_'+path,request('/'+path)[0]==410)
   quarantine=w/'quarantena';quarantine.mkdir();(site/'build').rename(quarantine/'build')
   (site/'build/assets').mkdir(parents=True);(site/'build/assets/app-fixture.js').write_text('/* NUOVO_ASSET_SINTETICO */')
   check('ritiro76_fuori_documentroot',sum(p.is_file() for p in (quarantine/'build/libs/@ckeditor').rglob('*'))==76 and not (site/'build/libs/@ckeditor').exists())
   check('vecchi_asset_preservati',all((quarantine/path).read_text()=='/* ASSET_SINTETICO */' for path in legacy))
   for path in legacy:
    check('dopo_ritiro410_'+path,request('/'+path)[0]==410)
   check('asset_necessario200',request('/build/assets/app-fixture.js')[0]==200)
   for path in ['/login','/password/reset','/w/'+32*'a','/checkin/'+64*'b']:
    check('front_preservato_'+path,request(path)[1].startswith(b'FRONT_OK:'))
   check('alias_register403',request('/register',headers={'Host':'alias.pilot.test'})[0]==403)
   check('IP_non_spoofabile_peer_non_fidato',request('/login',headers={'CF-Connecting-IP':'203.0.113.9','X-Forwarded-For':'203.0.113.8'})[1]==b'FRONT_OK:127.0.0.1')
   check('dotfile403',request('/.env')[0]==403)
   import socket
   body=w/'body-sintetico';body.write_bytes(b'x'*(8388608+1))
   p=subprocess.run(['curl','--silent','--show-error','--max-time','10','-H','Expect: 100-continue','-H','Content-Type: application/octet-stream','--data-binary','@'+str(body),'-o','/dev/null','-w','%{http_code}','http://127.0.0.1:8080/login'],capture_output=True)
   check('body_oltre8MiB_413',p.returncode==0 and p.stdout==b'413')
   with socket.create_connection(('127.0.0.1',8080),timeout=5) as client:
    client.sendall(b'POST /login HTTP/1.1\r\nHost: pilot.test\r\nTransfer-Encoding: chunked\r\nConnection: close\r\n\r\n1\r\nx\r\n0\r\n\r\n')
    raw=client.recv(1024)
   check('streaming_chunked411',b' 411 ' in raw.split(b'\r\n')[0])
   body.write_bytes(b'x'*(6*1024*1024+16384))
   p=subprocess.run(['curl','--silent','--show-error','--max-time','10','-H','Content-Type: application/octet-stream','--data-binary','@'+str(body),'-o','/dev/null','-w','%{http_code}','http://127.0.0.1:8080/login'],capture_output=True)
   check('body6MiB_con_margine_non_bloccato',p.returncode==0 and p.stdout==b'200')
  apache.terminate();apache.wait(timeout=15)
 backend.shutdown();report['esito']='PASS'
except Exception as e:report['errore']=str(e)
finally:
 for p in processes:
  if p.poll() is None:p.terminate();p.wait(timeout=15)
 report['processi_privati_fermati']=all(p.poll() is not None for p in processes)
 (w/'esito-sanitizzato.json').write_text(json.dumps(report,indent=2)+'\n');print(json.dumps(report))
 if report['esito']!='PASS':raise SystemExit(1)
