import {test,expect,BASE_URL} from '../Support/playwright.js';
import {requireIsolatedRuntime} from '../Support/playwright-isolation.js';
const runtime=requireIsolatedRuntime();
test.use({headless:true});
async function login(page, ids) {
 await page.goto(`${BASE_URL}/login`);
 await page.getByLabel(/Nome di accesso o email/i).fill(ids.manager.email);
 await page.getByLabel(/Password personale/i).fill('Password-audit-123!');
 await page.getByRole('button',{name:/Entra/i}).click();
 await expect(page).not.toHaveURL(/\/login$/);
 await page.goto(`${BASE_URL}/gestione-operativa`);
 return page.locator('input[name="_token"]').first().inputValue();
}
test('A14: token precedente alla disattivazione rifiutato dal modulo reale',async({page,context})=>{
 const ids=await (await page.request.get(`${BASE_URL}/account-session-ids.json`)).json();
 const csrf=await login(page,ids);
 const r=await page.request.put(`${BASE_URL}/gestione-operativa/utenti/${ids.recupero.id}`,{form:{_token:csrf,name:'Recupero sintetico',display_name:'Sintetico',shared_username:'recovery-sintetico',email:ids.recupero.email,ruolo_operativo:'reception',attivo:'0'},maxRedirects:0});
 expect(r.status()).toBe(302);
 await page.goto(`${BASE_URL}/logout`);
 await context.clearCookies();
 await page.goto(`${BASE_URL}/password/reset/${ids.recupero.token}?email=${encodeURIComponent(ids.recupero.email)}`);
 await page.getByLabel('Nuova password',{exact:true}).fill('Password-nuova-audit-123!');
 await page.getByLabel('Conferma password',{exact:true}).fill('Password-nuova-audit-123!');
 await page.getByRole('button',{name:'Salva nuova password'}).click();
 await expect(page.locator('.invalid-feedback')).toContainText('Il link di recupero non è valido');
 expect((await page.request.get(`${BASE_URL}/gestione-operativa`,{maxRedirects:0})).status()).toBe(302);
});
test('A15: POST Arrivi da sessione Chromium rifiuta cliente esterno e accetta quello proprio',async({page})=>{
 const ids=await (await page.request.get(`${BASE_URL}/account-session-ids.json`)).json();
 const csrf=await login(page,ids);
 const payload={_token:csrf,save_mode:'to_arrivi',name:'ARRIVO-BROWSER-SINTETICO',surname:'Audit',arrive:'2026-10-09',departure:'2026-10-11'};
 for(const id of [ids.clienti.estraneo,999999,'ID-INVALIDO']) {
  const r=await page.request.post(`${BASE_URL}/arrivi`,{form:{...payload,customer_id:id},headers:{Accept:'application/json'},maxRedirects:0});
  expect(r.status()).toBe(422);
  expect((await r.json()).errors).toHaveProperty('customer_id');
 }
 const r=await page.request.post(`${BASE_URL}/arrivi`,{form:{...payload,customer_id:ids.clienti.proprio},maxRedirects:0});
 expect(r.status()).toBe(302);
 const location=r.headers().location;
 expect(location).toBeTruthy();
 expect((await page.goto(location)).status()).toBe(200);
 await expect(page.locator('body')).toContainText('ARRIVO-BROWSER-SINTETICO');
});

for (const mode of ['ricordami-disattivo','ricordami-reset']) {
 test(`A12/A13: cookie Ricordami rifiutato dopo ${mode}`,async({page,browser})=>{
  const ids=await (await page.request.get(`${BASE_URL}/account-session-ids.json`)).json();
  const other=await browser.newContext({baseURL:runtime.origin,serviceWorkers:'block',proxy:{server:runtime.proxy,bypass:'<-loopback>'}});
  await other.route('**/*',async route=>{if(new URL(route.request().url()).origin!==runtime.origin){await route.abort('blockedbyclient');return;} await route.continue();});
  try {
   const victim=await other.newPage();
   await victim.goto(`${BASE_URL}/login`);
   const token=await victim.locator('input[name="_token"]').first().inputValue();
   const signed=await victim.request.post(`${BASE_URL}/login`,{form:{_token:token,login:ids[mode].email,password:'Password-audit-123!',remember:'1'},maxRedirects:0});
   expect(signed.status()).toBe(302);
   expect((await victim.goto(`${BASE_URL}/gestione-operativa`)).status()).toBe(200);
   const cookies=await other.cookies();
   expect(cookies.some(c=>c.name.startsWith('remember_'))).toBe(true);
   const csrf=await login(page,ids);
   const r=mode==='ricordami-reset'
    ?await page.request.post(`${BASE_URL}/gestione-operativa/utenti/${ids[mode].id}/password`,{form:{_token:csrf,password:'Password-nuova-audit-123!'},maxRedirects:0})
    :await page.request.put(`${BASE_URL}/gestione-operativa/utenti/${ids[mode].id}`,{form:{_token:csrf,name:'Ricordami sintetico',display_name:'Sintetico',shared_username:mode,email:ids[mode].email,ruolo_operativo:'reception',attivo:'0'},maxRedirects:0});
   expect(r.status()).toBe(302);
   await other.clearCookies();
   await other.addCookies(cookies.filter(c=>c.name.startsWith('remember_')));
   expect((await victim.request.get(`${BASE_URL}/gestione-operativa`,{maxRedirects:0})).status()).toBe(302);
   await victim.goto(`${BASE_URL}/gestione-operativa`);
   await expect(victim).toHaveURL(/\/login$/);
  } finally {await other.close();}
 });
}
test('A13: recupero valido revoca sessione precedente e mantiene quella nuova',async({page})=>{
 const ids=await (await page.request.get(`${BASE_URL}/account-session-ids.json`)).json();
 await page.goto(`${BASE_URL}/login`);
 await page.getByLabel(/Nome di accesso o email/i).fill(ids['recupero-valido'].email);
 await page.getByLabel(/Password personale/i).fill('Password-audit-123!');
 await page.getByRole('button',{name:/Entra/i}).click();
 expect((await page.goto(`${BASE_URL}/gestione-operativa`)).status()).toBe(200);
 const oldCookies=await page.context().cookies();
 await page.context().clearCookies();
 await page.goto(`${BASE_URL}/password/reset/${ids['recupero-valido'].token}?email=${encodeURIComponent(ids['recupero-valido'].email)}`);
 await page.getByLabel('Nuova password',{exact:true}).fill('Password-nuova-audit-123!');
 await page.getByLabel('Conferma password',{exact:true}).fill('Password-nuova-audit-123!');
 await page.getByRole('button',{name:'Salva nuova password'}).click();
 expect((await page.goto(`${BASE_URL}/gestione-operativa`)).status()).toBe(200);
 await page.context().clearCookies();
 await page.context().addCookies(oldCookies);
 expect((await page.request.get(`${BASE_URL}/gestione-operativa`,{maxRedirects:0})).status()).toBe(302);
});
