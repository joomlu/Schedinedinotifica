#!/usr/bin/python3
"""Delta Web Check-in: SMTP locale autenticato e solo resolver locale; ogni altra uscita resta negata."""
import grp,os,subprocess,sys
assert os.geteuid()==0
assert len(sys.argv)==2 and sys.argv[1] in ('apply','verify','remove')
gid=str(grp.getgrnam('schedine-pilot-egress').gr_gid)
chain='SCHEDINE_PILOT_EGRESS'
def call(args,ok=True):
 p=subprocess.run(args,capture_output=True,timeout=15)
 if ok and p.returncode:raise RuntimeError('Controllo firewall fallito: nessun fallback permissivo')
 return p
for binary,db in [('/usr/sbin/iptables-nft','127.0.0.1'),('/usr/sbin/ip6tables-nft','::1')]:
 jump=[binary,'-m','owner','--gid-owner',gid,'-j',chain]
 expected=['-N '+chain,'-A '+chain+' -d '+db+('/32' if binary.endswith('/iptables-nft') else '/128')+' -p tcp -m tcp --dport 3306 -j ACCEPT','-A '+chain+' -j REJECT --reject-with '+('icmp-port-unreachable' if binary.endswith('/iptables-nft') else 'icmp6-port-unreachable')]
 previous=list(expected)
 smtp_rules=[]
 if binary.endswith('/iptables-nft'):
  smtp_rules=[['-d','217.174.149.23','-p','tcp','--dport','465','-j','ACCEPT'],['-d','127.0.0.53','-p','udp','--dport','53','-j','ACCEPT'],['-d','127.0.0.53','-p','tcp','--dport','53','-j','ACCEPT']]
  expected[2:2]=['-A '+chain+' -d '+dst+'/32 -p '+proto+' -m '+proto+' --dport '+port+' -j ACCEPT' for dst,proto,port in [('217.174.149.23','tcp','465'),('127.0.0.53','udp','53'),('127.0.0.53','tcp','53')]]
 exists=call([binary,'-S',chain],False)
 if sys.argv[1]=='apply':
  if exists.returncode:
   call([binary,'-N',chain]);call([binary,'-A',chain,'-d',db,'-p','tcp','--dport','3306','-j','ACCEPT']);
   for rule in smtp_rules:call([binary,'-A',chain,*rule])
   call([binary,'-A',chain,'-j','REJECT'])
  else:
   actual=exists.stdout.decode().strip().splitlines()
   assert actual in [previous,expected],'Catena esistente diversa: STOP, non sovrascrivere'
   if actual==previous:
    for index,rule in enumerate(smtp_rules,2):call([binary,'-I',chain,str(index),*rule])
  if call([binary,'-C','OUTPUT',*jump[1:]],False).returncode:call([binary,'-I','OUTPUT','1',*jump[1:]])
 elif sys.argv[1]=='verify':
  assert exists.returncode==0 and exists.stdout.decode().strip().splitlines()==expected
  rules=call([binary,'-S','OUTPUT']).stdout.decode().splitlines()
  assert rules[1]=='-A OUTPUT -m owner --gid-owner '+gid+' -j '+chain,'Salto non prioritario: STOP'
 else:
  if exists.returncode==0:
   assert exists.stdout.decode().strip().splitlines()==expected,'Catena non propria: STOP'
   if call([binary,'-C','OUTPUT',*jump[1:]],False).returncode==0:call([binary,'-D','OUTPUT',*jump[1:]])
   call([binary,'-F',chain]);call([binary,'-X',chain])
print('Controllo egress pilota completato; nessuna policy globale modificata')
