import {test,expect,BASE_URL} from '../Support/playwright.js';
import {requireIsolatedRuntime} from '../Support/playwright-isolation.js';
const runtime=requireIsolatedRuntime();
test.use({headless:true});
async function contesto(browser) {
 const c=await browser.newContext({baseURL:runtime.origin,serviceWorkers:'block',proxy:{server:runtime.proxy,bypass:'<-loopback>'}});
 await c.route('**/*',async route=>{
  if(new URL(route.request().url()).origin!==runtime.origin) {await route.abort('blockedbyclient');return;}
  await route.continue();
 });
 return c;
}
async function login(page,name,password='Password-audit-123!') {
 await page.goto(`${BASE_URL}/login`);
 const ids=await (await page.request.get(`${BASE_URL}/baseline-acceptance-ids.json`)).json();
 await page.getByLabel(/Nome di accesso o email/i).fill(ids[name].email);
 await page.getByLabel(/Password personale/i).fill(password);
 await page.getByRole('button',{name:/Entra/i}).click();
 await expect(page).not.toHaveURL(/\/login$/);
 expect((await page.goto(`${BASE_URL}/gestione-operativa`)).status()).toBe(200);
}
async function negata(page) {
 const response=await page.request.get(`${BASE_URL}/gestione-operativa`,{maxRedirects:0});
 expect([302,401,403], 'La sessione precedente deve perdere accesso').toContain(response.status());
}
for(const mode of ['disattivazione','reset','cambio']) {
 test(`Sessioni indipendenti dopo ${mode}`,async({browser})=>{
  const a=await contesto(browser); const b=await contesto(browser);
  try {
   const operator=await a.newPage(); const victim=await b.newPage();
   await login(victim,mode);
   await login(operator,mode==='cambio'?mode:'manager');
   const ids=await (await operator.request.get(`${BASE_URL}/baseline-acceptance-ids.json`)).json();
   const csrf=await operator.locator('input[name="_token"]').first().inputValue();
   let response;
   if(mode==='disattivazione') {
    response=await operator.request.put(`${BASE_URL}/gestione-operativa/utenti/${ids[mode].id}`,{form:{_token:csrf,name:'Persona sintetica disattivata',display_name:'Sintetica',shared_username:'finale-disattivazione',email:ids[mode].email,ruolo_operativo:'reception',attivo:'0'},maxRedirects:0});
   } else if(mode==='reset') {
    response=await operator.request.post(`${BASE_URL}/gestione-operativa/utenti/${ids[mode].id}/password`,{form:{_token:csrf,password:'Password-nuova-audit-123!'},maxRedirects:0});
   } else {
    response=await operator.request.post(`${BASE_URL}/gestione-operativa/profilo/password`,{form:{_token:csrf,current_password:'Password-audit-123!',password:'Password-nuova-audit-123!',password_confirmation:'Password-nuova-audit-123!'},maxRedirects:0});
   }
   expect(response.status()).toBe(302);
   const errors=await operator.locator('.alert-danger').count();
   expect(errors).toBe(0);
   const fresh=await contesto(browser);
   try {
    const p=await fresh.newPage();
    if(mode==='disattivazione') {
     await p.goto(`${BASE_URL}/login`);
     const token=await p.locator('input[name="_token"]').first().inputValue();
     const denied=await p.request.post(`${BASE_URL}/login`,{form:{_token:token,login:ids[mode].email,password:'Password-audit-123!'},headers:{Accept:'application/json'}});
     expect(denied.status()).toBe(422);
    } else {await login(p,mode,'Password-nuova-audit-123!');}
   } finally {await fresh.close();}
   await negata(victim);
  } finally {await a.close();await b.close();}
 });
}
