import {test,expect,BASE_URL} from '../Support/playwright.js';
test.use({headless:true});
test.setTimeout(90000);
async function login(page){
 await page.goto(`${BASE_URL}/login`,{waitUntil:'domcontentloaded'});
 await page.getByLabel(/Nome di accesso o email/i).fill('accettazione-locale');
 await page.getByLabel(/Password personale/i).fill('Password-audit-123!');
 await page.getByRole('button',{name:/Entra/i}).click();
 await expect(page).not.toHaveURL(/\/login$/);
}
async function feedback(page,success=true){
 await page.waitForLoadState('domcontentloaded');
 const yes=page.getByRole('button',{name:/^Sì,/});
 if(await yes.isVisible()) {
  await yes.click();
  await expect(page.getByRole('button',{name:'OK',exact:true})).toBeVisible();
  await page.getByRole('button',{name:'OK',exact:true}).click();
  await page.waitForLoadState('domcontentloaded');
 }
 if(success){
  await expect(page.getByRole('heading',{name:'Operazione completata',exact:true})).toBeVisible();
  await page.getByRole('button',{name:'OK',exact:true}).click();
 }
}
async function state(page){ const r=await page.request.get(`${BASE_URL}/audit-local/state`);expect(r.status()).toBe(200);return r.json(); }
async function upload(page,url,csv,name,button){
 await page.goto(`${BASE_URL}${url}`,{waitUntil:'domcontentloaded'});
 await page.locator('input[name="file_import"]').setInputFiles({name,mimeType:'text/csv',buffer:Buffer.from(csv)});
 await page.getByRole('button',{name:button}).click();
 await feedback(page,!url.includes("componenti"));
}
test('Clienti: upload, annullamento, conferma persistente e duplicati',async({page})=>{
 const ids=await(await page.request.get(`${BASE_URL}/local-acceptance-ids.json`)).json();
 await login(page);
 await upload(page,'/clienti/import',ids.customerCsv,'clienti-annullamento.csv',/Carica e prepara verifica/);
 await expect(page).toHaveURL(/\/clienti\/import\/\d+$/);
 expect((await state(page)).customers).toHaveLength(0);
 page.once('dialog',dialog=>dialog.accept());
 await page.getByRole('button',{name:'Elimina importazione',exact:true}).click();
 await feedback(page);
 await expect(page).toHaveURL(/\/clienti\/import$/);
 expect((await state(page)).customers).toHaveLength(0);
 await upload(page,'/clienti/import',ids.customerCsv,'clienti-conferma.csv',/Carica e prepara verifica/);
 const batchUrl=page.url();
 await expect(page.locator('body')).toContainText('IMPORT-CLIENTE-UNO');
 await page.getByRole('button',{name:/Salva righe valide in Clienti/}).click();
 await feedback(page);
 await expect.poll(async()=> (await state(page)).customers.length).toBe(2);
 const first=(await state(page)).customers;
 await page.goto(batchUrl,{waitUntil:'domcontentloaded'});
 const save=page.getByRole('button',{name:/Salva righe valide in Clienti/});
 if(await save.isVisible()){await save.click();await feedback(page);}
 expect((await state(page)).customers).toEqual(first);
 await upload(page,'/clienti/import',ids.customerCsv,'clienti-duplicati.csv',/Carica e prepara verifica/);
 await expect(page.locator('body')).toContainText(/Duplicato/i);
 expect((await state(page)).customers).toEqual(first);
});
test('Componenti: import browser, annullamento, conferma monouso e conversione gruppo',async({page})=>{
 const ids=await(await page.request.get(`${BASE_URL}/local-acceptance-ids.json`)).json();
 // Compilazione anonima reale prima della gestione/importazione.
 await page.goto(`${BASE_URL}/checkin/${ids.full}`,{waitUntil:'domcontentloaded'});
 await page.locator('input[name="name"]').fill('PRINCIPALE-ACCETTAZIONE');
 await page.locator('button[name="save_mode"][value="web"]:visible').first().click();
 await feedback(page,false);
 await expect(page.locator('#schedina-form')).toHaveCount(0);
 await login(page);
 page.on('response',response=>{if(['document','xhr','fetch'].includes(response.request().resourceType())) console.log('CICLO_HTTP '+JSON.stringify({path:new URL(response.url()).pathname,status:response.status(),method:response.request().method()}));});
 const base=`/schedine/${ids.parent}/componenti/import`;
 const before=(await state(page)).components;
 await upload(page,base,ids.componentCsv,'componenti-annullamento.csv',/Genera preview/);
 await expect(page.locator('body')).toContainText('IMPORT-COMPONENTE');
 expect((await state(page)).components).toEqual(before);
 await page.getByRole('link',{name:'Torna ai componenti',exact:true}).click();
 expect((await state(page)).components).toEqual(before);
 await upload(page,base,ids.componentCsv,'componenti-conferma.csv',/Genera preview/);
 const token=await page.locator('input[name="import_batch_token"]').inputValue();
 await page.getByRole('button',{name:/Conferma importazione/}).click();
 await feedback(page);
 await expect.poll(async()=> (await state(page)).components.length).toBe(before.length+1);
 const imported=(await state(page)).components;
 expect(imported.find(c=>c.name==='IMPORT-COMPONENTE').date_nac).toBe('1980-01-01');
 await page.goto(`${BASE_URL}${base}`,{waitUntil:'domcontentloaded'});
 const csrf=await page.locator('input[name="_token"]').first().inputValue();
 const retry=await page.request.post(`${BASE_URL}${base}/conferma`,{form:{_token:csrf,import_batch_token:token},maxRedirects:0});
 expect(retry.status()).toBe(302);
 expect((await state(page)).components).toEqual(imported);
 await page.goto(`${BASE_URL}/schedine/${ids.parent}/modifica`,{waitUntil:'domcontentloaded'});
 await page.locator('input[name="cant_people"]').fill(String(imported.length+1));
 await page.locator('button[name="save_mode"][value="to_arrivi"]:visible').first().click();
 await feedback(page,false);
 await expect(page).toHaveURL(/\/arrivi$/);
 await expect.poll(async()=> (await state(page)).parent.circuito).toBe('arrivi');
 expect((await state(page)).components.map(c=>c.id)).toEqual(imported.map(c=>c.id));
 await page.goto(`${BASE_URL}/schedine/${ids.parent}/modifica`,{waitUntil:'domcontentloaded'});
 console.log('CAMPI_FULL',await page.locator('form').evaluateAll(fs=>Object.fromEntries(fs.flatMap(f=>Array.from(new FormData(f).entries())).filter(([k])=>/^(oa_|or_|customer_privacy|componenti)/.test(k)))));
 await page.locator('button[name="save_mode"][value="full"]:visible').first().click();
 await feedback(page,false);
 await expect.poll(async()=> (await state(page)).parent.circuito).toBe('schedina');
 const final=await state(page);
 expect(final.web_state).toBe('convertito');
 expect(final.parent.struttura_id).toBe(ids.structure);
 expect(final.parent.cant_people).toBe(imported.length+1);
 expect(final.parent.oa_country).toBe('ITALIA');
 expect(final.parent.or_country).toBe('ITALIA');
 expect(final.parent.or_doctype).toBe("CARTA DI IDENTITA'");
 expect(final.parent.or_typeaway).toBe('Via');
 expect(final.components.map(c=>c.id)).toEqual(imported.map(c=>c.id));
});
