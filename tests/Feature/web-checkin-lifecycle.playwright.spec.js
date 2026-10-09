import {test,expect,BASE_URL} from '../Support/playwright.js';
test.use({headless:true});
test.describe.configure({mode:'serial'});
async function ids(page){return (await page.request.get(`${BASE_URL}/web-lifecycle-ids.json`)).json();}
async function azione(page,button,confirmation,path){const response=page.waitForResponse(r=>r.url().endsWith(path)&&r.request().method()==='POST');await page.getByRole('button',{name:button,exact:true}).click();await page.getByRole('button',{name:confirmation,exact:true}).click();await page.getByRole('button',{name:'OK',exact:true}).click();expect((await response).status()).toBe(302);await page.waitForLoadState('load');const feedback=page.getByRole('button',{name:'OK',exact:true});if(await feedback.isVisible())await feedback.click();}
async function login(page){await page.goto(`${BASE_URL}/login`);await page.getByLabel(/Nome di accesso o email/i).fill('tassa-anteprima');await page.getByLabel(/Password personale/i).fill('Password-tassa-fixture-123!');await page.getByRole('button',{name:/Entra/i}).click();await expect(page).not.toHaveURL(/\/login$/);}
test('Ciclo reale: invito indipendente, form, salvataggio e correzione',async({page})=>{
 const r=await ids(page);expect(r.short).toHaveLength(32);expect(r.full).toHaveLength(64);
 for(const name of ['Ospite contratto','Ospite corretto']){
  expect((await page.goto(`${BASE_URL}/w/${r.short}`)).status()).toBe(200);await page.getByRole('link',{name:/Apri il tuo Web Check-in/}).click();
  await expect(page.locator('#schedina-form')).toBeVisible();await page.locator('#schedina-form input[name="name"]').fill(name);
  await page.locator('button[name="save_mode"][value="web"]:visible').first().click();const confirm=page.getByRole('button',{name:/Sì, salva/});if(await confirm.isVisible())await confirm.click();
  await expect(page.locator('#schedina-form')).toHaveCount(0);await expect(page.locator('body')).toContainText(name);
  await page.goto(`${BASE_URL}/checkin/${r.full}`,{waitUntil:'domcontentloaded'});await expect(page.locator('#schedina-form input[name="name"]')).toHaveValue(name);
 }
});
test('Conversione: ogni alias e completed contiene solo conferma; scaduti e revocati rifiutati; legacy80 valido',async({page})=>{
 const r=await ids(page);for(const path of [`/checkin/${r.converted.full}`,`/w/${r.converted.short}`,`/w/${r.converted.full}`,`/w/${r.converted.short}/completato`]){
  expect((await page.goto(BASE_URL+path)).status()).toBe(200);await expect(page.locator('body')).toHaveText('Check-in recibido');expect(await page.content()).not.toContain('Ospite');expect(await page.content()).not.toContain(r.full);
 }
 for(const kind of ['expired','revoked'])for(const token of [r[kind].full,r[kind].short])expect((await page.goto(`${BASE_URL}/w/${token}`)).status()).toBe(404);
 expect((await page.goto(`${BASE_URL}/checkin/${r.legacy.full}`)).status()).toBe(200);await expect(page.locator('#schedina-form')).toBeVisible();
});
test('Operatore reale: revoca, nuova emissione e invalidazione vecchi alias',async({page})=>{
 const r=await ids(page);await login(page);await page.goto(`${BASE_URL}/web-checkin/${r.id}/modifica`);
 await azione(page,'Revoca link','Sì, revoca','/revoca');expect((await page.request.get(`${BASE_URL}/w/${r.short}`)).status()).toBe(404);
 await azione(page,'Emetti nuovo link','Sì, emetti','/rigenera');await page.locator('#webcheckin-tab-accesso').click();const url=await page.locator('input[value*="/w/"]').inputValue();expect(url).not.toContain(r.short);expect(new URL(url).pathname.split('/').pop()).toHaveLength(32);
 expect((await page.request.get(`${BASE_URL}/checkin/${r.full}`)).status()).toBe(404);await page.goto(`${BASE_URL}/logout`);expect((await page.goto(url)).status()).toBe(200);
});
test('Riprogrammazione gestionale nel browser: attivo aggiornato e scaduto non riattivato',async({page})=>{
 const r=await ids(page);await login(page);
 for(const record of [r.reschedule,r.expired]){
  await page.goto(`${BASE_URL}/web-checkin/${record.id}/modifica`);const csrf=await page.locator('input[name="_token"]').first().inputValue();
  const payload={_token:csrf,_method:'PUT',numero_prenotazione:record.id===r.reschedule.id?'CONTRATTO-reschedule':'EFFIMERA',email:await page.locator('input[name="email"]').inputValue(),whatsapp:await page.locator('input[name="whatsapp"]').inputValue(),nome_referente:await page.locator('input[name="nome_referente"]').inputValue(),arrivo:r.dates.arrival,partenza:r.dates.departure,quantita_persone:1};
  expect((await page.request.post(`${BASE_URL}/web-checkin/${record.id}`,{form:payload,maxRedirects:0})).status()).toBe(302);
  expect((await page.request.get(`${BASE_URL}/w/${record.short}`)).status()).toBe(record.id===r.reschedule.id?200:404);
 }
 const state=await (await page.request.get(`${BASE_URL}/audit-token/state/reschedule`)).json();expect(state.request.link_expires_at).toBe(r.dates.expiry);
});
test('Conversione reale dall’interfaccia in Arrivo: dati operatore conservati, link senza dati',async({page})=>{
 const r=await ids(page);await login(page);await page.goto(`${BASE_URL}/schedine/${r.operational.parent}/modifica`,{waitUntil:'domcontentloaded'});
 await page.locator('button[name="save_mode"][value="to_arrivi"]:visible').first().click();await expect(page).toHaveURL(/\/arrivi$/);
 const state=await (await page.request.get(`${BASE_URL}/audit-token/state/operational`)).json();expect(state.request.stato).toBe('convertito');expect(state.parent.circuito).toBe('arrivi');expect(state.parent.name).toBe('OSPITE-operational');
 await page.goto(`${BASE_URL}/logout`);expect((await page.goto(`${BASE_URL}/w/${r.operational.short}`)).status()).toBe(200);await expect(page.locator('body')).toHaveText('Check-in recibido');
});
test('Cestino nel browser: cancellazione, ripristino bloccato, emissione autorizzata nuova',async({page})=>{
 const r=await ids(page);await login(page);await page.goto(`${BASE_URL}/web-checkin/${r.trash.id}/modifica`);let csrf=await page.locator('input[name="_token"]').first().inputValue();
 expect((await page.request.post(`${BASE_URL}/web-checkin/${r.trash.id}`,{form:{_token:csrf,_method:'DELETE'},maxRedirects:0})).status()).toBe(302);expect((await page.request.get(`${BASE_URL}/w/${r.trash.short}`)).status()).toBe(404);
 const deleted=await (await page.request.get(`${BASE_URL}/audit-token/state/trash`)).json();expect(deleted.request).toBeNull();await page.goto(`${BASE_URL}/cestino`);
 const restore=page.locator('button[title="Ripristina"]');await expect(restore).toBeVisible();console.log('ASSOCIAZIONE_RIPRISTINO',await restore.evaluate(b=>({action:b.form?.action,closest:b.closest('form')?.action})));expect(await restore.evaluate(b=>b.form?.action)).toBe(`${BASE_URL}/cestino/${deleted.item}/ripristina`);await restore.click();await page.getByRole('button',{name:/Sì, salva/}).click();await page.getByRole('button',{name:'OK',exact:true}).click();await page.waitForLoadState('load');
 await expect.poll(async()=>{const state=await(await page.request.get(`${BASE_URL}/audit-token/state/trash`)).json();return state.request?.id;}).toBeTruthy();
 const restored=await(await page.request.get(`${BASE_URL}/audit-token/state/trash`)).json();expect(restored.request.link_revoked_at).toBeTruthy();expect(restored.request.token).toHaveLength(64);expect((await page.request.get(`${BASE_URL}/checkin/${restored.request.token}`)).status()).toBe(404);
 await page.goto(`${BASE_URL}/web-checkin/${restored.request.id}/modifica`);await azione(page,'Emetti nuovo link','Sì, emetti','/rigenera');const fresh=await(await page.request.get(`${BASE_URL}/audit-token/state/trash`)).json();expect(fresh.request.short_token).toHaveLength(32);expect((await page.request.get(`${BASE_URL}/w/${fresh.request.short_token}`)).status()).toBe(200);expect((await page.request.get(`${BASE_URL}/w/${r.trash.short}`)).status()).toBe(404);
});
test('Limite letture reale: alias condivisi, altri ospiti e strutture Wi-Fi indipendenti',async({page})=>{
 const r=await ids(page);for(let i=0;i<60;i++)expect((await page.request.get(`${BASE_URL}/${i%2?'w/'+r.reads.short:'checkin/'+r.reads.full}`)).status()).toBe(200);
 const limited=await page.goto(`${BASE_URL}/w/${r.reads.short}`);expect(limited.status()).toBe(429);expect(Number(limited.headers()['retry-after'])).toBeGreaterThan(0);
 for(const kind of ['wifi2','wifi3'])expect((await page.goto(`${BASE_URL}/w/${r[kind].short}`)).status()).toBe(200);
});
test('Limite POST reale20/10min: CSRF valido, nessuna modifica, letture indipendenti',async({page})=>{
 const r=await ids(page);await page.goto(`${BASE_URL}/checkin/${r.legacy.full}`);const csrf=await page.locator('input[name="_token"]').first().inputValue();
 for(let i=0;i<20;i++){const response=await page.request.post(`${BASE_URL}/${i%2?'w/'+r.posts.short:'checkin/'+r.posts.full}`,{headers:{'X-CSRF-TOKEN':csrf},form:{name:'NON-MODIFICARE'}});expect(response.status()).toBe(200);expect(await response.text()).toBe('Check-in recibido');}
 const response=await page.request.post(`${BASE_URL}/w/${r.posts.short}`,{headers:{'X-CSRF-TOKEN':csrf},form:{name:'NON-MODIFICARE'}});expect(response.status()).toBe(429);expect(Number(response.headers()['retry-after'])).toBeGreaterThan(0);expect((await page.goto(`${BASE_URL}/w/${r.posts.short}`)).status()).toBe(200);
});
test('Limite generale Wi-Fi condiviso300 prima del lookup, anche altre strutture',async({page})=>{
 const r=await ids(page);let limited;for(let i=0;i<301;i++){const response=await page.request.get(`${BASE_URL}/w/invalid-${i}`);if(response.status()===429){limited=response;break;}expect(response.status()).toBe(404);}expect(limited).toBeTruthy();expect(Number(limited.headers()['retry-after'])).toBeGreaterThan(0);expect((await page.goto(`${BASE_URL}/w/${r.wifi3.short}`)).status()).toBe(429);
});
