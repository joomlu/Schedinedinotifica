import {test,expect,BASE_URL} from '../Support/playwright.js';
test.use({headless:true});
for(const width of [1440,768,390]) test(`Selezione esplicita cliente e bozza a ${width}px`,async({page})=>{
 await page.setViewportSize({width,height:900});
 const errors=[];page.on('pageerror',e=>errors.push(e.message));
 await page.goto(`${BASE_URL}/login`);await page.locator('[name="login"]').fill('fixture-super_admin');await page.locator('[name="password"]').fill('Password-fixture-123!');await page.getByRole('button',{name:'Entra',exact:true}).click();
 await page.goto(`${BASE_URL}/schedine/nuova`);
 const search=page.getByRole('combobox',{name:'Cerca un cliente esistente per nome o cognome'});
 await page.locator('[name="name"]').first().fill('Ada');await expect(page.getByRole('option',{name:/OmonimoFlusso Ada/})).toHaveCount(1);
 await page.locator('[name="surname"]').first().fill('OmonimoFlusso');await expect(page.getByRole('option',{name:/OmonimoFlusso/})).toHaveCount(2);
 await expect(page.locator('#customer_id')).toHaveValue('');
 page.on('dialog',d=>d.accept());await page.locator('[name="surname"]').first().press('ArrowDown');await page.locator('[name="surname"]').first().press('Enter');
 await expect(page.locator('#customer-selection-status')).toContainText('Cliente collegato: OmonimoFlusso Ada');
 await expect(page.locator('[name="name"]').first()).toHaveValue('Ada');
 await expect(page.locator('[name="arrive"]')).toHaveValue('');
 await page.getByRole('button',{name:/Continua con un cliente nuovo/}).click();await expect(page.locator('#customer_id')).toHaveValue('');await expect(page.locator('[name="name"]').first()).toHaveValue('Ada');
 let releaseOld, oldReady, oldDone;
 const delayed = new Promise(resolve=>{releaseOld=resolve;});
 const ready = new Promise(resolve=>{oldReady=resolve;});
 const done = new Promise(resolve=>{oldDone=resolve;});
 await page.route(url=>url.pathname==='/schedine/clienti/ricerca'&&url.searchParams.get('query')==='OmonimoFlusso',async route=>{
  const response=await route.fetch();oldReady();await delayed;
  try{await route.fulfill({response});}catch(error){if(!/cancel|close|abort|handled/i.test(error.message))throw error;}finally{oldDone();}
 });
 await search.fill('OmonimoFlusso');await ready;
 await search.fill('Bruno');await expect(page.getByRole('option',{name:/OmonimoFlusso Bruno/})).toHaveCount(1);
 releaseOld();await done;await expect(page.getByRole('option',{name:/OmonimoFlusso Ada/})).toHaveCount(0);
await expect(page.getByRole('option',{name:/OmonimoFlusso Bruno/})).toHaveCount(1);await page.getByRole('option',{name:/OmonimoFlusso Bruno/}).click();await expect(page.locator('[name="name"]').first()).toHaveValue('Bruno');
 await page.getByRole('button',{name:/Salva a bozza/}).click();await expect(page).toHaveURL(/schedine\/bozze/);
 await page.goto(`${BASE_URL}/schedine/bozze?q=OmonimoFlusso`);await page.getByRole('link',{name:'Riprendi compilazione',exact:true}).first().click();await expect(page.locator('[name="name"]').first()).toHaveValue('Bruno');
 expect(errors).toEqual([]);
 console.log(JSON.stringify({width,selezione:true,omonimi:true,tastiera:true,rimozione:true,bozza_ripresa:true}));
});
for(const width of [1440,768,390]) test(`Crea schedina dall’elenco, salva e modifica a ${width}px`,async({page})=>{
 let posts=0;page.on('request',r=>{if(r.method()==='POST'&&new URL(r.url()).pathname==='/schedine')posts++;});
 await page.setViewportSize({width,height:900});
 const errors=[];page.on('pageerror',e=>errors.push(e.message));
 await page.goto(`${BASE_URL}/login`);await page.locator('[name="login"]').fill('fixture-super_admin');await page.locator('[name="password"]').fill('Password-fixture-123!');await page.getByRole('button',{name:'Entra',exact:true}).click();
 await page.goto(`${BASE_URL}/clienti?q=OspiteCompletoFlusso`);await page.getByRole('link',{name:'Crea schedina',exact:true}).click();
 await expect(page.locator('[name="name"]').first()).toHaveValue('Persona');await expect(page.locator('[name="arrive"]')).toHaveValue('');
 await expect.poll(()=>page.locator('[name="arrive"]').evaluate(el=>Boolean(el._flatpickr))).toBe(true);
 await page.locator('[name="arrive"]').evaluate(el=>el._flatpickr.setDate('2026-11-10',true));
 await page.locator('[name="departure"]').evaluate(el=>el._flatpickr.setDate('2026-11-12',true));
 await page.locator('[name="room"]').fill('1');await page.locator('[name="beds"]').fill('2');
 await page.locator('#schedina-step-contact').click();
 await page.locator('[name="customer_email"]').fill('indirizzo-non-valido');
 await page.getByRole('button',{name:/Salva a schedina/}).click();
 await expect(page.locator('[name="customer_email"]')).toHaveAttribute('aria-invalid','true');
 await expect(page.locator('#schedina-step-contact')).toHaveAttribute('aria-selected','true');
 await page.getByRole('button',{name:'OK',exact:true}).click();
 await expect(page.locator('[name="arrive"]')).toHaveValue('2026-11-10');
 await expect(page.locator('[name="beds"]')).toHaveValue('2');
 await page.locator('[name="customer_email"]').fill('');
 await page.getByRole('button',{name:/Salva a schedina/}).click({clickCount:2});
 await expect(page).toHaveURL(/\/schedine$/);
 expect(posts).toBe(2);
 await page.goto(`${BASE_URL}/schedine?q=OspiteCompletoFlusso`);await page.getByRole('link',{name:'Modifica',exact:true}).first().click();
 await expect(page.locator('[name="beds"]')).toHaveValue('2');await expect(page.locator('[name="cant_people"]')).toHaveValue('1');
 await page.locator('[name="name"]').first().fill('Persona aggiornata');
 await page.getByRole('button',{name:/Salva a schedina/}).click();await expect(page).toHaveURL(/\/schedine$/);
 await page.goto(`${BASE_URL}/schedine?q=OspiteCompletoFlusso`);await page.getByRole('link',{name:'Modifica',exact:true}).first().click();await expect(page.locator('[name="name"]').first()).toHaveValue('Persona aggiornata');
 expect(errors).toEqual([]);console.log(JSON.stringify({width,ingresso_da_clienti:true,salvataggio_definitivo:true,modifica:true,letti_distinti:true}));
});

test.afterEach(async ({page}, info) => {
 if(info.status!==info.expectedStatus && !page.isClosed()) console.log('DIAGNOSI_SINTETICA',JSON.stringify(await page.evaluate(()=>({ready:document.readyState,UI:typeof window.UI,flatpickr:typeof window.flatpickr,arrive:document.querySelector('[name="arrive"]')?.outerHTML,scripts:[...document.scripts].filter(s=>s.type==='module').map(s=>s.src),errori:[...document.querySelectorAll('.invalid-feedback')].map(e=>e.textContent)}))));
});
