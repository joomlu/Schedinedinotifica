"""Server SMTPS sintetico: solo loopback del runtime isolato, nessun inoltro."""
import socket,ssl,sys,pathlib,json,base64,email,hashlib
from email import policy
root=pathlib.Path(sys.argv[1]);mode=sys.argv[2];server=socket.socket();server.bind(('127.0.0.1',0));server.listen(5)
ctx=ssl.SSLContext(ssl.PROTOCOL_TLS_SERVER);ctx.minimum_version=ssl.TLSVersion.TLSv1_2;ctx.load_cert_chain(str(root/'cert.pem'),str(root/'key.pem'))
print(json.dumps({'porta':server.getsockname()[1]}),flush=True)
while True:
 raw,_=server.accept()
 try:connection=ctx.wrap_socket(raw,server_side=True)
 except ssl.SSLError:raw.close();continue
 try:
  with connection,connection.makefile('rwb') as f:
   def reply(text):f.write((text+'\r\n').encode());f.flush()
   reply('220 smtp.fixture.invalid ESMTP');authenticated=False
   while True:
    line=f.readline()
    if not line:break
    cmd,*args=line.decode(errors='replace').strip().split(' ',1);arg=args[0]if args else '';cmd=cmd.upper()
    if cmd in ['EHLO','HELO']:reply('250-smtp.fixture.invalid\r\n250-AUTH PLAIN LOGIN\r\n250 SIZE 20000000')
    elif cmd=='AUTH':
     method,*data=arg.split(' ',1)
     if method.upper()=='PLAIN':
      if not data:reply('334 ');data=[f.readline().decode().strip()]
      auth=base64.b64decode(data[0]).split(b'\0');authenticated=auth[-2:]==[b'utente-sintetico',b'password-sintetica']
     elif method.upper()=='LOGIN':
      reply('334 VXNlcm5hbWU6');user=base64.b64decode(f.readline().strip());reply('334 UGFzc3dvcmQ6');password=base64.b64decode(f.readline().strip());authenticated=user==b'utente-sintetico' and password==b'password-sintetica'
     reply('235 Autenticazione sintetica' if authenticated else '535 Credenziali sintetiche errate')
    elif cmd in ['MAIL','RCPT']:reply('250 OK' if authenticated else '530 Autenticazione richiesta')
    elif cmd=='DATA':
     if not authenticated:reply('530 Autenticazione richiesta');continue
     reply('354 Fine con punto');chunks=[]
     while True:
      row=f.readline()
      if row in [b'.\r\n',b'.\n',b'']:break
      chunks.append(row[1:] if row.startswith(b'..') else row)
     mime=b''.join(chunks);(root/'ricevuto.eml').write_bytes(mime);message=email.message_from_bytes(mime,policy=policy.default)
     html=[p.get_content() for p in message.walk() if p.get_content_type()=='text/html'];text=[p.get_content() for p in message.walk() if p.get_content_type()=='text/plain'];images=[p for p in message.walk() if p.get_content_maintype()=='image']
     proof={'autenticato':authenticated,'HTML':len(html)==1,'testo':len(text)==1,'logo_CID':len(images)==1 and bool(images[0]['Content-ID']),'MIME_sha256':hashlib.sha256(mime).hexdigest(),'modo':mode};(root/'ricevuta.json').write_text(json.dumps(proof))
     if mode=='incerto':break
     reply('250 Messaggio sintetico accettato' if mode=='accetta' else '550 Messaggio sintetico rifiutato')
    elif cmd=='QUIT':reply('221 Chiusura');break
    else:reply('250 OK')
 except (BrokenPipeError,ConnectionError,ssl.SSLError):pass
