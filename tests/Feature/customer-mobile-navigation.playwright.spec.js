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
for(const round of [1,2,3,4,5,6,7,8]) test(`Navigazione mobile ripetuta e tastiera ciclo ${round}`,async({page})=>{
 await page.setViewportSize({width:390,height:900});
 await login(page);
 const errors=[];page.on('pageerror',e=>errors.push(e.message));
 for(let attempt=0;attempt<10;attempt++){
  await page.goto(`${BASE_URL}/clienti/nuovo`);await page.waitForLoadState('networkidle');
  expect(await page.evaluate(()=>getComputedStyle(document.documentElement).scrollBehavior)).toBe('auto');
  await page.locator('[name="type_cliente"]').selectOption('Richiesta');
  await page.locator('[name="name"]').fill('Persona');
  await page.locator('[name="surname"]').fill('Sintetica');
  for(const id of ['steparrow-description-info','steparrow-azienda-info','steparrow-contact-info','steparrow-gen-info']){
   // Alterna il clic immediato originale e una pressione reale di 80 ms; nessun retry.
   await page.locator(`#${id}-tab`).click(attempt%2?{delay:80}:{});
   await expect(page.locator(`#${id}`)).toBeVisible();
  }
  await page.locator('#steparrow-gen-info-tab').press('ArrowRight');
  await expect(page.locator('#steparrow-description-info')).toBeVisible();
  await page.locator('#steparrow-description-info-tab').press('End');
  await expect(page.locator('#steparrow-contact-info')).toBeVisible();
  await page.locator('#steparrow-contact-info-tab').press('Home');
  await expect(page.locator('#steparrow-gen-info')).toBeVisible();
 }
 expect(errors).toEqual([]);
});
