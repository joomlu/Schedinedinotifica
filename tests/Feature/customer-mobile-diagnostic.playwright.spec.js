import {test as isolatedTest, expect, BASE_URL} from '../Support/playwright.js';
import {withCustomerObservation} from '../Support/customer-observation.js';
const test=withCustomerObservation(isolatedTest);
test.use({headless:true}); test.setTimeout(180000);
async function login(page) {
 await page.goto(`${BASE_URL}/login`);
 await page.locator('[name="login"]').fill('fixture-super_admin');
 await page.locator('[name="password"]').fill('Password-fixture-123!');
 await page.getByRole('button',{name:'Entra',exact:true}).click();
 await expect(page).not.toHaveURL(/\/login$/);
 await page.goto(`${BASE_URL}/clienti/nuovo`);
 await page.waitForLoadState('networkidle');
 if(await page.getByRole('button',{name:/Struttura: Hotel Browser(?! B)/}).count()===0){
 await page.getByRole('button',{name:/Struttura:/}).click();
 await page.getByRole('button',{name:'Hotel Browser',exact:true}).click();
 const confirm = page.getByRole('button',{name:'Sì, salva',exact:true});
 if(await confirm.isVisible()) await accept(page);
 await page.waitForLoadState('networkidle');

 }
 await expect(page.getByRole('button',{name:/Struttura: Hotel Browser(?! B)/})).toBeVisible();
}
async function accept(page, label='Sì, salva') {
 await page.getByRole('button',{name:label,exact:true}).click();
 await page.getByRole('button',{name:'OK',exact:true}).click();
 await page.waitForLoadState('networkidle');
 const ok=page.getByRole('button',{name:'OK',exact:true});
 if(await ok.isVisible()) await ok.click();
}
for(const round of [1,2,3,4,5,6]) test(`Riproduzione Anagrafica mobile ciclo ${round}`,async({page})=>{
 const width=390; await page.setViewportSize({width,height:900});
 const errors=[];page.on('pageerror',e=>{errors.push(e.message); console.log('Errore browser sintetico:', e.message);});
 await login(page);
 for(const contact of ['email','phone','cellphone']) {
  await page.goto(`${BASE_URL}/clienti/nuovo`); await page.waitForLoadState('networkidle');
  await page.locator('[name="type_cliente"]').selectOption('Richiesta');
  await page.locator('[name="name"]').fill('Persona');
  await page.locator('[name="surname"]').fill('Sintetica');
  await expect(page.locator('#steparrow-gen-info button[type="button"]', {hasText:'Anagrafica'})).toHaveCount(0);
  await page.locator('#steparrow-description-info-tab').click();
  await expect(page.locator('#steparrow-description-info')).toBeVisible();
  await expect(page).toHaveURL(/\/clienti\/nuovo$/);
  await page.locator('#steparrow-contact-info-tab').click();
  await page.getByRole('button',{name:/Salva a cliente/}).last().click();
  await expect(page).toHaveURL(/\/clienti\/nuovo$/);
  const ok=page.getByRole('button',{name:'OK',exact:true});if(await ok.isVisible())await ok.click();
  await page.locator(`[name="${contact}"]`).fill(contact==='email'?'persona@example.test':'+39 333 1234567');
  await page.getByRole('button',{name:/Salva a cliente/}).last().click();
  await accept(page);
  await expect(page).toHaveURL(/\/clienti$/);
  await expect(page.locator('body')).toContainText('Sintetica');
  await page.locator('a[href$="/modifica"]').first().click();
  await expect(page.locator('[name="type_cliente"]')).toHaveValue('Richiesta');
  await page.waitForLoadState('networkidle');
  await page.locator('#steparrow-contact-info-tab').click();
  await expect(page.locator('#steparrow-contact-info')).toBeVisible();
  await expect(page.locator(`[name="${contact}"]`)).toHaveValue(contact==='email'?'persona@example.test':'+39 333 1234567');
  await expect(page.locator('#privacy_consent_switch')).not.toBeChecked();
 }
 expect(await page.evaluate(()=>document.documentElement.scrollWidth<=window.innerWidth)).toBeTruthy();
 expect(errors).toEqual([]);
});
