import pathlib,json,sys,subprocess,os
P=pathlib.Path;r=P(sys.argv[1]);m=json.loads((r/'identity.json').read_bytes());sys.path.insert(0,m['server_kit']);import s0_core as c,probe,finalize_operation
p=json.loads(c.private(r/'piano-run.json').read_bytes());s=json.loads(c.private(P(p['control_root'])/'state.json').read_bytes());app=P(p['app_root'])
assert s['decision']=='CANDIDATO_ACCETTATO' and s['stopped'] and not P(p['flag']).exists()
assert not (app/'storage/framework/down').exists() and not (app/'storage/framework/maintenance.php').exists()
args=['runuser','-u','tanggosoftware','--','git','-C',str(app)];env={**os.environ,'GIT_OPTIONAL_LOCKS':'0'}
sha=subprocess.check_output(args+['rev-parse','HEAD'],env=env).decode().strip();assert sha==m['sha'];assert not subprocess.check_output(args+['status','--porcelain'],env=env).strip()
proof=json.loads((app/'docs/evidenze/navigazione-schedine-2026-10-17/verifica.json').read_bytes())
for category in ['sorgenti_sha256','protetti_sha256']:
 for name,digest in proof[category].items():assert c.digest(app/name)==m['sorgenti_attesi'][name],name
new_proof=json.loads((app/'docs/evidenze/completamento-nuova-schedina-2026-10-18/verifica.json').read_bytes())
for name,digest in new_proof['sorgenti_sha256'].items():assert c.digest(app/name)==digest,name
assert subprocess.run(['systemctl','is-active','--quiet','schedine-release-fpm.service']).returncode==0
body=probe.run(p['fpm_socket'],p['private_probe'],p['probe_key'],s);assert body['version']=='12.69.3' and body['tables']==64 and body['key_hash']==s['baseline_key_hash'] and body['questura'] is False and body['istat'] is False and body['mail']=='array'
sonde=finalize_operation.public_checks(p);assert all(sonde.values());assert c.digest(p['private_probe'])==m['probe_sha256']
guardiani={}
for sig in ['094f49c0e2d10e62','c2f375a6e85ba8b8']:
 unit='schedine-c5-guard-'+sig+'.service';props=dict(x.split('=',1) for x in subprocess.check_output(['systemctl','show',unit,'-p','ActiveState','-p','UnitFileState']).decode().splitlines());assert props=={'ActiveState':'inactive','UnitFileState':'disabled'},props;guardiani[unit]=props
v={'guardiani_conclusi':guardiani,'esito':'PASS_STATO_FINALE_ONLINE','SHA':sha,'Git_pulito':True,'sorgenti_e_protetti_conformi':True,'FPM_attivo':True,'barriere_assenti':True,'key_provider_mail_conformi':True,'sonde9':sonde,'verifica_solo_lettura':True};print(json.dumps(v))
