import {test,expect,BASE_URL} from '../Support/playwright.js';
test.use({headless:true});
test('Link breve generato: invito visibile e apertura del Web Check-in',async({page})=>{
 const ids=await (await page.request.get(`${BASE_URL}/web-short-ids.json`)).json();
 await page.goto(`${BASE_URL}/login`);
 await page.getByLabel(/Nome di accesso o email/i).fill('tassa-anteprima');
 await page.getByLabel(/Password personale/i).fill('Password-tassa-fixture-123!');
 await page.getByRole('button',{name:/Entra/i}).click();
 await expect(page).not.toHaveURL(/\/login$/);
 await page.goto(`${BASE_URL}/web-checkin/${ids.web}/modifica`);
 await page.locator("#webcheckin-tab-accesso").click();
 const generated=await page.locator('input[value*="/w/"]').inputValue();
 expect(generated).toBe(`${BASE_URL}/w/${ids.short}`);
 await page.goto(`${BASE_URL}/logout`);
 const response=await page.goto(generated);
 expect(response.status()).toBe(200);
 await expect(page.getByRole('heading',{name:/Benvenuto in/})).toBeVisible();
 await page.getByRole('link',{name:/Apri il tuo Web Check-in/}).click();
 await expect(page).toHaveURL(`${BASE_URL}/checkin/${ids.full}`);
 await expect(page.locator('body')).toContainText('Anteprima positivo');
 expect((await page.request.get(`${BASE_URL}/w/INESISTENTE-aaaaaaaa`)).status()).toBe(404);
});

test('Link breve pending: form reale, salvataggio e riapertura',async({page})=>{
 const ids=await (await page.request.get(`${BASE_URL}/web-short-ids.json`)).json();
 expect((await page.goto(`${BASE_URL}/w/${ids.pending}`)).status()).toBe(200);
 await page.getByRole('link',{name:/Apri il tuo Web Check-in/}).click();
 await expect(page.locator('#schedina-form')).toBeVisible();
 await page.locator('#schedina-form input[name="name"]').fill('Web sintetico salvato');
 await page.locator('button[name="save_mode"][value="web"]:visible').first().click();
 const confirm=page.getByRole('button',{name:/Sì, salva/});
 if(await confirm.isVisible()) await confirm.click();
 await expect(page.locator('#schedina-form')).toHaveCount(0);
 await expect(page.locator('body')).toContainText('Web sintetico salvato');
 expect((await page.goto(`${BASE_URL}/checkin/${ids.pendingFull}`)).status()).toBe(200);
 await expect(page.locator('#schedina-form input[name="name"]')).toHaveValue('Web sintetico salvato');
});
test('Link breve parent estero e token errati: nessuna esposizione',async({page})=>{
 const ids=await (await page.request.get(`${BASE_URL}/web-short-ids.json`)).json();
 for(const key of [ids.foreign,'MISSING-aaaaaaaa','SHORTPENDING-c','SHORTPENDING-________']) {
  expect((await page.goto(`${BASE_URL}/w/${key}`)).status()).toBe(404);
  await expect(page.locator('body')).not.toContainText('RISERVATO-ALTRO-TENANT');
  expect((await page.request.post(`${BASE_URL}/w/${key}`,{form:{name:'NON-SCRIVERE'}})).status()).toBe(419);
 }
});
