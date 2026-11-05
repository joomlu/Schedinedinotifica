import {test,expect,BASE_URL} from '../Support/playwright.js';
test.use({headless:true});
test.setTimeout(60000);
async function login(page,email) {
 await page.goto(`${BASE_URL}/login`);
 await page.getByLabel(/Nome di accesso o email/i).fill(email);
 await page.getByLabel(/Password personale/i).fill('Password-audit-123!');
 await page.getByRole('button',{name:/Entra/i}).click();
 await expect(page).not.toHaveURL(/\/login$/);
}
test('A16: creazione dal modulo legacy, elenco e accesso del nuovo account',async({page})=>{
 const ids=await (await page.request.get(`${BASE_URL}/legacy-user-ids.json`)).json();
 await login(page,ids.manager.email);
 expect((await page.goto(`${BASE_URL}/strutture/utenti/create`)).status()).toBe(200);
 const form=page.locator('form[action$="/strutture/utenti"]');
 await expect(form).toBeVisible();
 await form.locator('input[name="name"]').fill('UTENTE-LEGACY-BROWSER-SINTETICO');
 await form.locator('input[name="email"]').fill('legacy-browser@example.invalid');
 await form.locator('input[name="password"]').fill('Password-audit-123!');
 await expect(form.locator(':invalid')).toHaveCount(0);
 await Promise.all([
  page.waitForResponse(r=>r.url().endsWith('/strutture/utenti') && r.request().method()==='POST').then(r=>expect(r.status()).toBe(302)),
  (async()=>{
   await form.getByRole('button',{name:'Crea',exact:true}).click();
   await page.getByRole('button',{name:'Sì, salva',exact:true}).click();
   await page.getByRole('button',{name:'OK',exact:true}).click();
  })(),
 ]);
 await expect(page).toHaveURL(/\/strutture\/utenti$/);
 await page.waitForLoadState('load');
 await expect(page.getByRole('heading',{name:'Operazione completata',exact:true})).toBeVisible();
 await page.getByRole('button',{name:'OK',exact:true}).click();
 const row=page.getByRole('row').filter({hasText:'legacy-browser@example.invalid'});
 await expect(row).toHaveCount(1);
 await expect(row).toContainText('struttura_user');
 await page.goto(`${BASE_URL}/logout`);
 await login(page,'legacy-browser@example.invalid');
 expect((await page.goto(`${BASE_URL}/gestione-operativa`)).status()).toBe(200);
 expect((await page.request.get(`${BASE_URL}/strutture/utenti/create`,{maxRedirects:0})).status()).toBe(403);
});
test('A16: reception non crea account tramite POST forgiato',async({page})=>{
 const ids=await (await page.request.get(`${BASE_URL}/legacy-user-ids.json`)).json();
 await login(page,ids.reception.email);
 expect((await page.goto(`${BASE_URL}/gestione-operativa`)).status()).toBe(200);
 const csrf=await page.locator('input[name="_token"]').first().inputValue();
 expect((await page.request.get(`${BASE_URL}/strutture/utenti/create`,{maxRedirects:0})).status()).toBe(403);
 const r=await page.request.post(`${BASE_URL}/strutture/utenti`,{form:{_token:csrf,name:'NON-CREARE-BROWSER',email:'legacy-forged-browser@example.invalid',password:'Password-audit-123!',ruolo:'super_admin',ruolo_operativo:'proprietario',struttura_id:ids.estranea},maxRedirects:0});
 expect(r.status()).toBe(403);
 await page.goto(`${BASE_URL}/logout`);
 await login(page,ids.manager.email);
 expect((await page.goto(`${BASE_URL}/strutture/utenti`)).status()).toBe(200);
 await expect(page.locator('body')).not.toContainText('legacy-forged-browser@example.invalid');
});
