import {test, expect, BASE_URL} from '../Support/playwright.js';

test.use({headless:true});
 test.setTimeout(120000);

const IMAGE=Buffer.from('iVBORw0KGgoAAAANSUhEUgAAAAIAAAACCAIAAAD91JpzAAAACXBIWXMAAA7EAAAOxAGVKw4bAAAAC0lEQVQImWNgQAYAAA4AAbGa6gYAAAAASUVORK5CYII=','base64');

const login=async(page,role)=>{await page.goto(`${BASE_URL}/login`);
await page.locator('[name="login"]').fill(`fixture-${role}`);
await page.locator('[name="password"]').fill('Password-fixture-123!');
await page.getByRole('button',{name:'Entra',exact:true}).click();
await expect(page).not.toHaveURL(/\/login$/);
await page.waitForLoadState('networkidle');
};

const finish=async(page,label)=>{await page.getByRole('button',{name:label,exact:true}).click();
await Promise.all([page.waitForNavigation(),page.getByRole('button',{name:'OK',exact:true}).click()]);
const ok=page.getByRole('button',{name:'OK',exact:true});
if(await ok.isVisible())await ok.click();
await page.waitForLoadState('networkidle');
};

const search=async(page,id,value)=>{await page.locator(id).fill(value);
await Promise.all([page.waitForNavigation(),page.locator(id).press('Enter')]);
await page.waitForLoadState('networkidle');
};


test('Cataloghi privati: CRUD, modali, stato, pagine, isolamento e mobile',async({page})=>{
 const errors=[];
page.on('pageerror',e=>errors.push(e.message));
await login(page,'super_admin');

 const records=[];

 for(const c of [{url:'/rilasciato',table:'#rilasciatoTable',search:'#rilasciatoSearch',modal:'Rilasciato',key:'name'},{url:'/tipovia',table:'#tipoviaTable',search:'#tipoviaSearch',modal:'Tipovia',key:'abbr'}]){
  await page.goto(BASE_URL+c.url);
await expect(page.locator(c.table+' tr[data-id]')).toHaveCount(10);

  await Promise.all([page.waitForNavigation({waitUntil:'load'}),page.locator('.pagination a').filter({hasText:'2'}).click()]);
await page.waitForLoadState('networkidle');
await expect(page).toHaveURL(/page=2/);
await expect(page.locator(c.table+' tr[data-id]')).toHaveCount(10);

  await page.locator(`[data-bs-target="#modal${c.modal}Create"]`).click();
const modal=page.locator(`#modal${c.modal}Create`);
await modal.locator(`[name="${c.key}"]`).fill('Annullato');
await expect(modal.locator('.modal-dialog')).toHaveCSS('transform','none');
await modal.getByRole('button',{name:'Chiudi',exact:true}).click();
await expect(modal).not.toBeVisible();

  await page.locator(`[data-bs-target="#modal${c.modal}Create"]`).click();
await expect(modal.locator(`[name="${c.key}"]`)).toHaveValue('');

  await modal.locator(`[name="${c.key}"]`).fill('PrivataBrowser');
if(c.key==='abbr')await modal.getByRole('switch').uncheck();
await modal.getByRole('button',{name:'Salva',exact:true}).click();
await finish(page,'Sì, salva');

  await search(page,c.search,'PrivataBrowser');
await expect(page.locator(c.table+' tr[data-id]')).toHaveCount(1);
if(c.key==='abbr')await expect(page.locator(c.table)).toContainText('Disattivo');

  await page.locator(`[data-bs-target="#modal${c.modal}Create"]`).click();
  await modal.locator(`[name="${c.key}"]`).fill('PrivataBrowser');
  await modal.getByRole('button',{name:'Salva',exact:true}).click();await finish(page,'Sì, salva');
  await expect(modal).toBeVisible();await expect(modal.locator(`[name="${c.key}"]`)).toHaveValue('PrivataBrowser');
  await expect(modal.locator('.modal-dialog')).toHaveCSS('transform','none');
await modal.getByRole('button',{name:'Chiudi',exact:true}).click();await expect(modal).not.toBeVisible();
  const id=await page.locator(c.table+' tr[data-id]').getAttribute('data-id');
records.push({...c,id});

  await page.locator(`[data-bs-target="#modal${c.modal}Edit${id}"]`).click();
const edit=page.locator(`#modal${c.modal}Edit${id}`);
await expect(edit.locator('form input[name="_method"]')).toHaveValue('PUT');
await edit.locator(`[name="${c.key}"]`).fill('ModificataBrowser');
await edit.getByRole('button',{name:'Salva',exact:true}).click();
await finish(page,'Sì, modifica');

  await search(page,c.search,'ModificataBrowser');
await expect(page.locator(c.table+' tr[data-id]')).toHaveCount(1);

  await page.setViewportSize({width:390,height:844});
expect(await page.evaluate(()=>document.documentElement.scrollWidth)).toBeLessThanOrEqual(390);
await page.setViewportSize({width:1440,height:1000});

 }
 await page.goto(`${BASE_URL}/logout`);
await login(page,'struttura_user');

 for(const c of records){await page.goto(BASE_URL+c.url+'?q=ModificataBrowser');
await expect(page.locator(c.table+' tr[data-id]')).toHaveCount(0);
const token=await page.locator('input[name="_token"]').first().inputValue();
const url=c.key==='name'?'/rilasciato/':'/tipovia_update/';
expect((await page.request.put(BASE_URL+url+c.id,{form:{[c.key]:'Attacco',_token:token}})).status()).toBe(404);
}
 await page.waitForLoadState('networkidle');
 expect(errors).toEqual([]);

});


test('Geo Comuni: logo, sostituzione, persistenza, rimozione e dinieghi',async({page})=>{
 const errors=[];
page.on('pageerror',e=>errors.push(e.message));
await login(page,'admin');
await page.goto(`${BASE_URL}/geo/comuni/logo?q=Comune+Fixture`);

 const row=page.locator('tbody tr').filter({hasText:'Comune Fixture Logo'});
let previous=await row.locator('img').getAttribute('src');

 for(const name of ['primo.png','secondo.png']){
  await row.locator('[name="logo"]').setInputFiles({name,mimeType:'image/png',buffer:IMAGE});
await row.getByRole('button',{name:'Carica',exact:true}).click();
await finish(page,'Sì, salva');

  const image=page.getByAltText('Logo Comune Fixture Logo');
await expect.poll(()=>image.evaluate(i=>i.complete&&i.naturalWidth>0)).toBe(true);
const next=await image.getAttribute('src');
expect(next).not.toBe(previous);
previous=next;
await page.reload();
await expect(image).toHaveAttribute('src',next);

 }
 await page.goto(`${BASE_URL}/logout`);
await login(page,'super_admin');
await page.goto(`${BASE_URL}/geo/comuni/logo?q=Comune+Fixture`);
await expect(page.getByAltText('Logo Comune Fixture Logo')).toHaveAttribute('src',previous);

 const ids=await(await page.request.get(`${BASE_URL}/configurazioni-fixture.json`)).json();
const token=await row.locator('[name="_token"]').first().inputValue();
expect((await page.request.post(`${BASE_URL}/strutture/${ids.b}/seleziona`,{form:{_token:token}})).ok()).toBe(true);
await page.goto(`${BASE_URL}/geo/comuni/logo?q=Comune+Fixture`);
await expect(page.getByAltText('Logo Comune Fixture Logo')).toHaveAttribute('src',previous);

 await page.setViewportSize({width:390,height:844});
expect(await page.evaluate(()=>document.documentElement.scrollWidth)).toBeLessThanOrEqual(390);
await page.setViewportSize({width:1440,height:1000});

 const search=page.locator('#geo-comuni-search');
await search.fill('inesistente');
await expect(page.locator('tbody')).toContainText('Nessun comune trovato');
await search.fill('Comune Fixture');
await expect(row).toBeVisible();

 await row.getByRole('button',{name:'Rimuovi',exact:true}).click();
await finish(page,'Sì, elimina');
await expect(page.locator('tbody')).toContainText('Nessun logo');

 await page.goto(`${BASE_URL}/logout`);
await login(page,'struttura_user');
expect((await page.goto(`${BASE_URL}/geo/comuni/logo`)).status()).toBe(403);

 await page.waitForLoadState('networkidle');
 expect(errors).toEqual([]);

});
