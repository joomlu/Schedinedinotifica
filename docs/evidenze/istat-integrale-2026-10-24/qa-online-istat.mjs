import {chromium} from 'playwright';
import {expect} from '@playwright/test';
import fs from 'node:fs';
import crypto from 'node:crypto';
const O=new URL('./',import.meta.url),f=JSON.parse(fs.readFileSync(new URL('fixture-privata.json',O))),base='https://schedinedinotifica.tanggo.software';
const browser=await chromium.launch({headless:true});
let denied=0;const result={inizio:new Date().toISOString(),prove:[],dichiarazioni_statistiche:0};
async function context(width){const c=await browser.newContext({viewport:{width,height:1000},serviceWorkers:'block'});c.setDefaultTimeout(20000);c.setDefaultNavigationTimeout(30000);await c.route('**/*',async route=>{const r=route.request(),u=new URL(r.url());if(u.origin!==base&&!['data:','about:'].includes(u.protocol))return route.abort();if(r.method()!=='GET'&&r.method()!=='HEAD'&&!['/login','/logout','/istat-tabella-a/download/xml','/istat-tabella-a/ws/verify'].includes(u.pathname)){denied++;return route.abort();}await route.continue();});return c;}
async function login(p,role){await p.context().clearCookies();await p.goto(base+'/login');await p.locator('[name=login]').fill(f.accessi[role].username);await p.locator('[name=password]').fill(f.accessi[role].password);await p.getByRole('button',{name:'Entra',exact:true}).click();await expect(p).not.toHaveURL(/\/login$/);}
async function post(p,path){return p.evaluate(async path=>{const token=document.querySelector('meta[name=csrf-token]')?.content||document.querySelector('[name=_token]')?.value;const d=new FormData();d.set('_token',token);d.set('mese','2026-04');const r=await fetch(path,{method:'POST',body:d});return {status:r.status,body:await r.text(),cache:r.headers.get('cache-control')};},path);}
try{
 let storedPath;
 for(const width of [1440,768,390]){
  const c=await context(width),p=await c.newPage(),errors=[];p.on('pageerror',e=>errors.push(e.message));
  await login(p,'hotel');expect((await p.goto(base+'/istat-tabella-a?mese=2026-04')).status()).toBe(200);
  await expect(p.locator('#istat-username')).toHaveValue('');await expect(p.locator('#istat-password')).toHaveValue('');await expect(p.getByRole('button',{name:'Invia direttamente',exact:true})).toBeDisabled();
  const generated=await post(p,'/istat-tabella-a/download/xml');expect(generated.status).toBe(200);expect(generated.cache).toContain('no-store');expect(generated.body).toContain('<movimenti>');
  const parsed=await p.evaluate(xml=>{const d=new DOMParser().parseFromString(xml,'application/xml');return {errors:d.querySelectorAll('parsererror').length,giornate:d.querySelectorAll('movimenti > movimento').length,arrivi:d.querySelectorAll('arrivi > arrivo').length,partenze:d.querySelectorAll('partenze > partenza').length,camere:Array.from(d.querySelectorAll('struttura > camereoccupate')).reduce((n,e)=>n+Number(e.textContent),0)};},generated.body);
  expect(parsed).toEqual({errors:0,giornate:30,arrivi:2,partenze:2,camere:2});
  fs.writeFileSync(new URL('xml-sintetico-'+width+'.xml',O),generated.body);
  const validated=await post(p,'/istat-tabella-a/ws/verify');expect(validated.status).toBe(200);expect(validated.body).toContain('XML verificato localmente');
  await p.goto(base+'/istat-tabella-a?mese=2026-04&tab=storico-xml');await expect(p.locator('#istat-pane-storico-xml')).toContainText('Ross1000 Emilia-Romagna XML-WS 2.4');await expect(p.locator('#istat-pane-storico-xml')).toContainText('Origine invariata');
  const href=await p.locator('#istat-pane-storico-xml a[href*="/download/storico/"]').first().getAttribute('href');storedPath=new URL(href).pathname;expect(new URL(href).origin).toBe(base);
  const saved=await p.evaluate(async path=>{const r=await fetch(path);return {status:r.status,body:await r.text(),cache:r.headers.get('cache-control')};},storedPath);expect(saved.status).toBe(200);expect(saved.body).toBe(generated.body);expect(saved.cache).toContain('no-store');
  expect(await p.evaluate(()=>document.documentElement.scrollWidth<=innerWidth+1)).toBe(true);await p.screenshot({path:new URL('istat-storico-'+width+'.png',O).pathname,fullPage:true});
  await p.goto(base+'/istat-tabella-a/stampa-riepilogo?mese=2026-04');await expect(p.getByRole('heading',{name:'Provenienze per residenza'})).toBeVisible();await expect(p.locator('body')).toContainText('Italia - Lazio');await expect(p.locator('body')).toContainText('GERMANIA');
  const rows=p.locator('.section').last().locator('tbody tr');await expect(rows).toHaveCount(2);for(let i=0;i<2;i++)expect((await rows.nth(i).locator('td').allTextContents()).slice(-3).map(x=>x.trim())).toEqual(['1','2','1']);
  await p.screenshot({path:new URL('istat-riepilogo-'+width+'.png',O).pathname,fullPage:true});
  expect((await p.goto(base+'/istat-tabella-a?mese=2026-04&sid='+f.strutture.B)).status()).toBe(403);await p.goto(base+'/istat-tabella-a?mese=2026-04');expect((await post(p,'/istat-tabella-a/download/xml?sid='+f.strutture.B)).status).toBe(403);
  expect(errors).toEqual([]);await c.close();result.prove.push({larghezza:width,XML:parsed,sha256:crypto.createHash('sha256').update(generated.body).digest('hex'),presenze_riepilogo:4,stesso_XML_storico:true,validazione_locale:true,invio_disabilitato:true,tenant_estraneo403:true});
 }
 const c=await context(1440),p=await c.newPage();await login(p,'hotelB');expect((await p.goto(base+storedPath)).status()).toBe(404);await login(p,'reception');expect((await p.goto(base+'/istat-tabella-a?mese=2026-04')).status()).toBe(200);await expect(p.getByRole('button',{name:'Invia direttamente',exact:true})).toBeDisabled();await c.close();expect(denied).toBe(0);
 result.esito='PASS_ISTAT_ONLINE';result.storico_altro_tenant404=true;result.reception_lettura=true;result.POST_estranei=denied;result.fine=new Date().toISOString();fs.writeFileSync(new URL('online-istat.json',O),JSON.stringify(result,null,2));console.log(JSON.stringify(result));
}finally{await browser.close();}
