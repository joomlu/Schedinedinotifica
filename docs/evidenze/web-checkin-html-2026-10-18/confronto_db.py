"""Confronto conservativo del dump S0 con una sola migrazione additiva."""
import re,hashlib

def verify(before:bytes, after:bytes, migration:str, tables:list[str]):
 assert re.fullmatch(r'[a-z0-9_]+',migration)
 assert tables==[], 'Non sono autorizzate nuove tabelle'
 pattern=rb'^DROP TABLE IF EXISTS `([^`]+)`;'
 def blocks(raw):
  points=list(re.finditer(pattern,raw,re.M));assert points
  return raw[:points[0].start()],{x.group(1).decode():raw[x.start():points[i+1].start() if i+1<len(points) else len(raw)] for i,x in enumerate(points)}
 h0,b0=blocks(before);h1,b1=blocks(after)
 assert h0==h1,'Intestazione DB cambiata'
 assert set(b1)==set(b0), 'Elenco tabelle cambiato'
 for name in tables:assert not re.search(rb'^INSERT INTO `'+name.encode()+rb'`',b1[name],re.M),'Nuova tabella non vuota prima delle fixture'
 old=b0['migrations'];new=b1['migrations'];name=migration.encode()
 row=re.compile(rb"^INSERT INTO `migrations` VALUES \(([0-9]+),'"+name+rb"',([0-9]+)\);\n",re.M)
 added=list(row.finditer(new));assert len(added)==1,'Registrazione migrazione non esattamente unica'
 auto=re.compile(rb'AUTO_INCREMENT=([0-9]+)')
 a0=auto.findall(old);a1=auto.findall(new);assert len(a0)==len(a1)==1
 assert int(a1[0])==int(a0[0])+1 and int(added[0].group(1))==int(a0[0]),'Sequenza migrazioni diversa da una sola aggiunta'
 batches=[int(x) for x in re.findall(rb"^INSERT INTO `migrations` VALUES \([0-9]+,'[^']+',([0-9]+)\);$",old,re.M)]
 assert batches and int(added[0].group(2))==max(batches)+1,'Batch migrazione inatteso'
 cleaned=row.sub(b'',new)
 cleaned=auto.sub(b'AUTO_INCREMENT='+a0[0],cleaned,count=1)
 assert cleaned==old,'Schema o righe migrazioni preesistenti cambiati'
 additions={
  'struttura':[('web_checkin_colore', rb'varchar\(7\) DEFAULT NULL')],
  'web_checkin_modelli':[('impostazioni',rb'text DEFAULT NULL'),('deleted_at',rb'timestamp NULL DEFAULT NULL')],
  'web_checkin_comunicazioni':[(n,rb'timestamp NULL DEFAULT NULL') for n in ['nascosta_at','tentativo_at','accettato_at']]+[('errore_codice',rb'varchar\(40\) DEFAULT NULL')],
 }
 for n,v in b0.items():
  if n=='migrations':continue
  current=b1[n]
  if n in additions:
   cols=lambda block:re.findall(rb'^  `([^`]+)` ',block,re.M)
   assert cols(current)==cols(v)+[c.encode() for c,_ in additions[n]],'Ordine/colonne non autorizzati: '+n
   for column,definition in additions[n]:
    line=re.compile(rb'^  `'+column.encode()+rb'` '+definition+rb',\n',re.M)
    assert len(line.findall(current))==1,'Definizione colonna inattesa: '+n+'.'+column
    current=line.sub(b'',current)
   insert=re.compile(rb'^INSERT INTO `'+n.encode()+rb'` VALUES \(.*\);$',re.M)
   suffix=b',NULL'*len(additions[n])
   def strip_null(match):
    values=match.group(0)
    assert values.endswith(suffix+b');'),'Valore nuovo non NULL: '+n
    return values[:-len(suffix)-2]+b');'
   current=insert.sub(strip_null,current)
  assert current==v,'Schema, indici, contatori o dati preesistenti cambiati: '+n
 return {'esito':'PASS_DUMP_S0_CON_SOLA_MIGRAZIONE_ADDITIVA','tabelle_preesistenti_identiche':len(b0),'colonne_nullable_aggiunte':{n:[c for c,_ in a] for n,a in additions.items()},'migrazione':migration,'SHA_dump_S0':hashlib.sha256(before).hexdigest(),'SHA_dump_attuale':hashlib.sha256(after).hexdigest()}
