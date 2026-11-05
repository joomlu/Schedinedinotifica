import {test,expect,BASE_URL} from '../Support/playwright.js';
test.use({headless:true});
const login=async page=>{
 await page.goto(`${BASE_URL}/login`);await page.locator('[name="login"]').fill('fixture-super_admin');await page.locator('[name="password"]').fill('Password-fixture-123!');await page.getByRole('button',{name:'Entra',exact:true}).click();
};
for(const width of [1440,768,390]) test(`Arrivi: salvataggio, cancellazione conferma e cambio giorno a ${width}px`,async({page})=>{
 await page.setViewportSize({width,height:900});await login(page);
 const errors=[];page.on('pageerror',e=>errors.push(e.message));
 await page.goto(`${BASE_URL}/arrivi/nuovo`);await expect(page).toHaveURL(/schedine\/nuova\?arrival=1/);
 await page.locator('[name="name"]').first().fill(`ArriviBrowser${width}`);await page.locator('[name="surname"]').first().fill('Sintetico');
 await expect.poll(()=>page.locator('[name="departure"]').evaluate(el=>Boolean(el._flatpickr))).toBe(true);
 await page.locator('[name="departure"]').evaluate(el=>el._flatpickr.setDate('2027-01-02',true));
 await page.getByRole('button',{name:/Salva in Arrivi/}).first().click();await expect(page).toHaveURL(/\/arrivi$/);await page.getByRole('button',{name:'OK',exact:true}).click();
 const row=page.locator('tr').filter({hasText:`ArriviBrowser${width}`});await expect(row).toHaveCount(1);
 const edit=await row.getByTitle('Apri arrivo nella scheda').getAttribute('href');
 await page.clock.install();await page.goto(new URL(edit,BASE_URL).href);
 const before=await page.locator('[name="name"]').first().inputValue();let posts=0;page.on('request',r=>{if(r.method()==='POST') posts++;});
 page.once('dialog',d=>d.dismiss());await page.getByRole('button',{name:/Passa a schedina/}).first().click();
 await expect(page.locator('[name="name"]').first()).toHaveValue(before);expect(posts).toBe(0);await expect(page).toHaveURL(/\/modifica(?:\?.*)?$/);
 await page.locator('[name="name"]').first().fill(`${before} conservato`);
 await page.route('**/arrivi/giorno-operativo*',async route=>{await route.fulfill({json:{today:'2027-01-02',timezone:'Europe/Rome',epoch_ms:Date.parse('2027-01-02T23:59:58+01:00'),next_day_ms:Date.parse('2027-01-03T00:00:00+01:00')}});});
 await page.evaluate(()=>window.dispatchEvent(new Event('pageshow')));await expect(page.locator('[name="arrive"]')).toHaveValue('2027-01-02');
 await page.clock.runFor(3000);await expect(page.locator('[name="arrive"]')).toHaveValue('2027-01-03');
 await page.evaluate(()=>window.dispatchEvent(new Event('pageshow')));await page.clock.runFor(1000);await expect(page.locator('[name="arrive"]')).toHaveValue('2027-01-03');
 await expect(page.locator('[name="departure"]')).toHaveValue('2027-01-02');await expect(page.locator('#arrivi-day-warning')).toBeVisible();
 await expect(page.locator('[name="name"]').first()).toHaveValue(`${before} conservato`);
 expect(await page.evaluate(()=>document.documentElement.scrollWidth<=innerWidth)).toBe(true);expect(errors).toEqual([]);
 console.log(JSON.stringify({width,cancel_zero_scritture:true,mezzanotte_senza_perdita:true,partenza_invariata:true}));
});
test('Passaggio confermato da due schede: una sola schedina e rifiuto della scheda vecchia',async({page,context})=>{
 await login(page);await page.goto(`${BASE_URL}/clienti?q=OspiteCompletoFlusso`);await page.getByRole('link',{name:'Crea schedina',exact:true}).click();
 await expect.poll(()=>page.locator('[name="departure"]').evaluate(el=>Boolean(el._flatpickr))).toBe(true);
 await page.locator('[name="departure"]').evaluate(el=>el._flatpickr.setDate('2026-11-12',true));
 await page.locator('[name="room"]').fill('1');await page.locator('[name="beds"]').fill('2');
 await page.getByRole('button',{name:/Salva in Arrivi/}).first().click();await expect(page).toHaveURL(/\/arrivi$/);await page.getByRole('button',{name:'OK',exact:true}).click();
 const row=page.locator('tr').filter({hasText:'OspiteCompletoFlusso'});await row.getByTitle('Apri arrivo nella scheda').click();
 const edit=page.url(),id=edit.match(/schedine\/(\d+)\/modifica/)[1];
 const second=await context.newPage();await second.goto(edit);
 page.once('dialog',d=>d.accept());await page.getByRole('button',{name:/Passa a schedina/}).first().click();await expect(page).toHaveURL(/\/schedine$/);
 await page.goto(`${BASE_URL}/schedine?q=OspiteCompletoFlusso`);await expect(page.locator('tr').filter({hasText:'OspiteCompletoFlusso'})).toHaveCount(1);
 const retry=await page.request.post(`${BASE_URL}/arrivi/${id}/a-schedina`,{form:{_token:await second.locator('[name="_token"]').first().inputValue(),confirm_conversion:'1'}});expect(retry.ok()).toBe(true);
 const stale=await second.evaluate(async()=>{const form=document.getElementById('schedina-form');const data=new FormData(form);data.set('save_mode','to_arrivi');const res=await fetch(form.action,{method:'POST',headers:{Accept:'application/json'},body:data});return res.status;});expect(stale).toBe(409);
 await page.goto(`${BASE_URL}/arrivi?q=OspiteCompletoFlusso`);await expect(page.locator('tr').filter({hasText:'OspiteCompletoFlusso'})).toHaveCount(0);
 await second.close();console.log(JSON.stringify({conversione_confermata:true,una_sola_schedina:true,retry_senza_duplicati:true,scheda_vecchia_409:true}));
});


test('Elenco Arrivi scaduti: ricerca filtrata senza overflow a 1440, 768 e 390px',async({page})=>{
 await login(page);await page.goto(`${BASE_URL}/arrivi/nuovo`);
 await page.locator('[name="name"]').first().fill('ArriviElencoMobile');await page.locator('[name="surname"]').first().fill('Sintetico');
 await expect.poll(()=>page.locator('[name="departure"]').evaluate(el=>Boolean(el._flatpickr))).toBe(true);
 await page.locator('[name="departure"]').evaluate(el=>el._flatpickr.setDate('2020-01-02',true));
 await page.getByRole('button',{name:/Salva in Arrivi/}).first().click();await expect(page).toHaveURL(/\/arrivi$/);await page.getByRole('button',{name:'OK',exact:true}).click();
 for(const width of [1440,768,390]) {
  await page.setViewportSize({width,height:900});await page.goto(`${BASE_URL}/arrivi?q=ArriviElencoMobile`);
  await expect(page.locator('#arriviSearch')).toHaveValue('ArriviElencoMobile');
  await expect.poll(()=>page.evaluate(()=>document.documentElement.scrollWidth<=innerWidth)).toBe(true);
  const row=page.locator('tr').filter({hasText:'ArriviElencoMobile'});await expect(row).toHaveCount(1);
  await expect(row).toHaveAttribute('data-partenza','2/1/20');await expect(row.getByText('Partenza da correggere')).toBeVisible();
  for(const path of ['/schedine?q=ArriviElencoMobile','/schedine/bozze?q=ArriviElencoMobile']) {
   await page.goto(BASE_URL+path);await expect.poll(()=>page.evaluate(()=>document.documentElement.scrollWidth<=innerWidth)).toBe(true);
  }
 }
 await page.goto(`${BASE_URL}/arrivi?q=ArriviElencoMobile`);
 await page.locator('tr').filter({hasText:'ArriviElencoMobile'}).getByTitle('Apri arrivo nella scheda').click();await expect(page.locator('#arrivi-day-warning')).toBeVisible();
});
