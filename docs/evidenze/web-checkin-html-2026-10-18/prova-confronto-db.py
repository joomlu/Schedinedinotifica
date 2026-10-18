from pathlib import Path
import importlib.util,json
p=Path(__file__).resolve().parent
spec=importlib.util.spec_from_file_location('confronto',p/'confronto_db.py');m=importlib.util.module_from_spec(spec);spec.loader.exec_module(m)
name='2026_10_18_200000_extend_web_checkin_inviti'
def block(n,columns,rows,auto=5):
 return ('DROP TABLE IF EXISTS `'+n+'`;\nCREATE TABLE `'+n+'` (\n'+''.join('  `'+c+'` '+t+',\n' for c,t in columns)+'  PRIMARY KEY (`id`)\n) ENGINE=InnoDB AUTO_INCREMENT='+str(auto)+';\n'+''.join('INSERT INTO `'+n+'` VALUES ('+r+');\n' for r in rows)).encode()
base=[('id','bigint unsigned NOT NULL'),('contenuto','text DEFAULT NULL')]
adds={'struttura':[('web_checkin_colore','varchar(7) DEFAULT NULL')],'web_checkin_modelli':[('impostazioni','text DEFAULT NULL'),('deleted_at','timestamp NULL DEFAULT NULL')],'web_checkin_comunicazioni':[(n,'timestamp NULL DEFAULT NULL') for n in ['nascosta_at','tentativo_at','accettato_at']]+[('errore_codice','varchar(40) DEFAULT NULL')]}
old=b'-- intestazione\n'+block('migrations',[('id','int NOT NULL'),('migration','varchar(255) NOT NULL'),('batch','int NOT NULL')],["1,'storica',1"],2)
new=b'-- intestazione\n'+block('migrations',[('id','int NOT NULL'),('migration','varchar(255) NOT NULL'),('batch','int NOT NULL')],["1,'storica',1",f"2,'{name}',2"],3)
for n,a in adds.items():old+=block(n,base,["1,'Testo, NULL e \\\'virgolette\\\''"]);new+=block(n,base+a,["1,'Testo, NULL e \\\'virgolette\\\''"+',NULL'*len(a)])
old+=block('estranea',base,["1,'immutata'"]);new+=block('estranea',base,["1,'immutata'"])
m.verify(old,new,name,[])
mutations={
 'dato_precedente':new.replace(b'immutata',b'alterata'),
 'nuovo_valore':new.replace(b',NULL);',b",'x');",1),
 'colonna_tipo':new.replace(b'varchar(7)',b'varchar(8)'),
 'default_non_nullo':new.replace(b'varchar(7) DEFAULT NULL',b"varchar(7) DEFAULT '#123456'"),
 'indice':new.replace(b'PRIMARY KEY (`id`)',b'KEY (`id`)',1),
 'contatore':new.replace(b'AUTO_INCREMENT=5',b'AUTO_INCREMENT=6',1),
 'migrazione_batch':new.replace((name+"',2").encode(),(name+"',3").encode()),
 'migrazione_nome':new.replace(name.encode(),b'estranea'),
 'colonna_rimossa':new.replace(b'  `deleted_at` timestamp NULL DEFAULT NULL,\n',b''),
 'tabella_aggiunta':new+block('abusiva',base,[]),
 'intestazione':new.replace(b'intestazione',b'alterata'),
}
for label,raw in mutations.items():
 try:m.verify(old,raw,name,[])
 except AssertionError:continue
 raise AssertionError('Mutazione non respinta: '+label)
(p/'prova-confronto-db.json').write_text(json.dumps({'esito':'PASS','positivo_sintetico':1,'mutazioni_respinte':list(mutations),'limite':'Prova del comparatore; non è un confronto S0 reale.'},indent=2));print('PASS: confronto positivo e 11 mutazioni respinte')
