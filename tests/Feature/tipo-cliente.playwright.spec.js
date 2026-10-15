import {test, expect, BASE_URL} from '../Support/playwright.js';
test.use({headless:true}); test.setTimeout(120000);
async function login(page,role){await page.goto(`${BASE_URL}/login`);await page.locator('input[name="login"]').fill(`fixture-${role}`);await page.locator('input[name="password"]').fill('Password-fixture-123!');await page.getByRole('button',{name:'Entra',exact:true}).click();await expect(page).not.toHaveURL(/\/login$/);}
test('Tipo cliente: consultazione, ricerca, nessuna azione o modale e selettori clienti/schedine',async({page})=>{
 const errors=[];page.on('pageerror',e=>errors.push(e.message));
 await login(page,'super_admin');await page.goto(`${BASE_URL}/tipo_cliente`);
 await expect(page.getByText('Classificazioni predefinite del sistema, non modificabili.',{exact:true})).toBeVisible();
 await expect(page.locator('#tipoClienteTable tr[data-id]')).toHaveCount(3);
 await expect(page.locator('#tipoClienteTable')).toContainText('OSPITE');
 await expect(page.locator('#tipoClienteTable')).toContainText('COMPONENTE');
 await expect(page.locator('#tipoClienteTable')).toContainText('RICHIESTA');
 await expect(page.locator('#tipoClienteTable button')).toHaveCount(0);
 await expect(page.locator('[id^="modalTipoCliente"]')).toHaveCount(0);
 await expect(page.getByRole('button',{name:'Nuovo Tipo Cliente'})).toHaveCount(0);
 await page.locator('#tipoClienteSearch').fill('richiesta');
 await Promise.all([page.waitForNavigation(),page.locator('#tipoClienteSearch').press('Enter')]);
 await expect(page.locator('#tipoClienteTable tr[data-id]')).toHaveCount(1);
 await page.locator('#tipoClienteSearchClear').click();await expect(page.locator('#tipoClienteTable tr[data-id]')).toHaveCount(3);
 const ids=await(await page.request.get(`${BASE_URL}/tipo-cliente-fixture-ids.json`)).json();
 await page.goto(`${BASE_URL}/clienti/${ids.cliente}/modifica`);
 await expect(page.locator('select[name="type_cliente"]').first()).toHaveValue('Richiesta');
 for(const text of ['Ospite','Componente','Richiesta'])await expect(page.locator('select[name="type_cliente"]').first()).toContainText(text);
 await expect(page.locator('select[name="type_cliente"]').first()).not.toContainText('Extra sintetico da conservare');
 await page.goto(`${BASE_URL}/schedine/${ids.schedina}/modifica`);
 await expect(page.locator('input[name="customer_type_housed"]').first()).toHaveValue('Richiesta');
 await page.goto(`${BASE_URL}/logout`);await login(page,'struttura_user');await page.goto(`${BASE_URL}/tipo_cliente`);
 await expect(page.locator('#tipoClienteTable tr[data-id]')).toHaveCount(3);
 await page.goto(`${BASE_URL}/clienti/${ids.cliente}/modifica`);await expect(page.locator('body')).toContainText('404');
 expect(errors).toEqual([]);
});
test('Clienti: creazione e modifica delle tre classificazioni, bozza e annullamento della conferma',async({page})=>{
 await login(page,'super_admin');
 await page.goto(`${BASE_URL}/clienti/nuovo`);
 await page.waitForLoadState('networkidle');
 if(await page.getByRole('button',{name:/Struttura: Hotel Browser(?! B)/}).count()===0){
 await page.getByRole('button',{name:/Struttura:/}).click();
 await page.getByRole('button',{name:'Hotel Browser',exact:true}).click();
 const selectionConfirm=page.getByRole('button',{name:'Sì, salva',exact:true});
 if(await selectionConfirm.isVisible()){
  await selectionConfirm.click();
  await page.getByRole('button',{name:'OK',exact:true}).click();
  await page.waitForLoadState('networkidle');
  const selectionOk=page.getByRole('button',{name:'OK',exact:true});if(await selectionOk.isVisible())await selectionOk.click();
 }
 }
 for(const type of ['Ospite','Componente','Richiesta']){
  await page.goto(`${BASE_URL}/clienti/nuovo`);
  await page.locator('select[name="type_cliente"]').first().selectOption(type);
  await page.locator('input[name="name"]').first().fill('Persona');
  await page.locator('input[name="surname"]').first().fill('Sintetica');
  if(type==='Richiesta'){
   await page.locator('#steparrow-contact-info-tab').click();
   await page.locator('[name="email"]').fill('persona@example.test');
   await page.locator('#steparrow-gen-info-tab').click();
  }
  await page.getByRole('button',{name:/Salva a bozza/}).first().click();
  await expect(page.getByRole('button',{name:'Sì, salva bozza',exact:true})).toBeVisible();
  await page.getByRole('button',{name:'Annulla',exact:true}).click();
  await expect(page).toHaveURL(/\/clienti\/nuovo$/);
  await expect(page.locator('select[name="type_cliente"]').first()).toHaveValue(type);
  await page.getByRole('button',{name:/Salva a bozza/}).first().click();
  await page.getByRole('button',{name:'Sì, salva bozza',exact:true}).click();
  await Promise.all([page.waitForNavigation(),page.getByRole('button',{name:'OK',exact:true}).click()]);
  console.log('Esito bozza sintetica',type,page.url(),await page.locator('[data-server-alert="error"]').allTextContents());
  await expect(page).toHaveURL(/\/clienti\/\d+\/modifica/);
  const ok=page.getByRole('button',{name:'OK',exact:true});if(await ok.isVisible())await ok.click();
  await expect(page.locator('select[name="type_cliente"]').first()).toHaveValue(type);
  await page.locator('input[name="name"]').first().fill('Aggiornata');
  await page.getByRole('button',{name:/Salva a bozza/}).first().click();
  await page.getByRole('button',{name:'Sì, salva bozza',exact:true}).click();
  await Promise.all([page.waitForNavigation(),page.getByRole('button',{name:'OK',exact:true}).click()]);
  if(await ok.isVisible())await ok.click();
  await expect(page.locator('input[name="name"]').first()).toHaveValue('Aggiornata');
  await expect(page.locator('select[name="type_cliente"]').first()).toHaveValue(type);
 }
});
