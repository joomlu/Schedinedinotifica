"""Protocollo autenticato: cifrati soli, custodia chiave separata, ricevuta fresca."""
import hashlib,hmac,json,os,pathlib,time
P=pathlib.Path
NAMES={'db.sql.enc','fs.tar.gz.enc','inventory.json.enc'}

def canonical(v):return json.dumps(v,sort_keys=True,separators=(',',':')).encode()
def sign(v,key):return hmac.digest(key,b'schedine-transfer-final-v1\0'+canonical(v),'sha256').hex()
def sha(p):
 h=hashlib.sha256()
 with P(p).open('rb') as f:
  for b in iter(lambda:f.read(1024*1024),b''):h.update(b)
 return h.hexdigest()
def offer(seals,state,volume_uuid,source_mode='production'):
 return {'nonce':state['nonce'],'sha':state['sha'],'boot_id':state['boot_id'],'t0':state['t0_monotonic'],
         'source_mode':source_mode,'remaining_s0_seconds':600-(time.monotonic()-state['t0_monotonic']),
         'volume_uuid':volume_uuid,'files':{n+'.enc':{'sha256':s['cipher_sha256'],'byte':s['byte']} for n,s in seals.items()}}
def verify_offer(v):
 if v.get('source_mode') not in ('production','fixture') or not 0<v.get('remaining_s0_seconds',0)<=600:raise RuntimeError('Sorgente/budget offerta non conformi')
 if set(v['files'])!=NAMES or len(v['nonce'])!=48 or any(x not in '0123456789abcdef' for x in v['nonce']):raise RuntimeError('Offerta non valida')
 for n,s in v['files'].items():
  if s['byte']<=0 or len(s['sha256'])!=64:raise RuntimeError('Cifrato non valido')
def verify_files(v,folder):
 verify_offer(v)
 for n,s in v['files'].items():
  p=P(folder)/n
  if p.is_symlink() or not p.is_file() or p.stat().st_size!=s['byte'] or sha(p)!=s['sha256']:raise RuntimeError('Trasferimento alterato')
def receipt(v,reread,key,volume_uuid,offhost,restore_proof):
 verify_files(v,reread)
 if volume_uuid!=v['volume_uuid'] or offhost is not True:raise RuntimeError('Destinazione fuori host non attestata')
 if restore_proof.get('complete') is not True:raise RuntimeError('Recupero ricevente incompleto')
 data={'offer_sha256':hashlib.sha256(canonical(v)).hexdigest(),'nonce':v['nonce'],'sha':v['sha'],
       'boot_id':v['boot_id'],'t0':v['t0'],'volume_uuid':volume_uuid,'offhost':True,
       'cipher_hashes':{n:s['sha256'] for n,s in v['files'].items()},'restore_proof':restore_proof}
 return {'data':data,'mac':sign(data,key)}
def verify_receipt(r,v,key,seen):
 verify_offer(v);d=r['data']
 if not hmac.compare_digest(sign(d,key),r['mac']):raise RuntimeError('Ricevuta non autenticata')
 if d['offer_sha256']!=hashlib.sha256(canonical(v)).hexdigest() or any(d[k]!=v[k] for k in ['nonce','sha','boot_id','t0','volume_uuid']):raise RuntimeError('Ricevuta di altra operazione')
 if d['nonce'] in seen:raise RuntimeError('Ricevuta riutilizzata')
 if d['offhost'] is not True or d['restore_proof'].get('complete') is not True:raise RuntimeError('Ricevuta incompleta')
 if d['cipher_hashes']!={n:s['sha256'] for n,s in v['files'].items()}:raise RuntimeError('Hash ricevuta differente')
 seen.add(d['nonce']);return d
