import {test, expect, BASE_URL} from '../Support/playwright.js';
test.use({headless:true});test.setTimeout(120000);
test('Wizard cliente: tutte le schede senza overflow a 390 pixel',async({page})=>{
 await page.setViewportSize({width:390,height:900});
 await page.goto(`${BASE_URL}/login`);
 await page.locator('[name="login"]').fill('fixture-super_admin');
 await page.locator('[name="password"]').fill('Password-fixture-123!');
 await page.getByRole('button',{name:'Entra',exact:true}).click();
 await expect(page).not.toHaveURL(/\/login$/);
 let customerWrites=0;page.on('request',request=>{if(['POST','PUT','PATCH','DELETE'].includes(request.method()) && new URL(request.url()).pathname.startsWith('/clienti'))customerWrites++;});
 const ids=await(await page.request.get(`${BASE_URL}/wizard-fixture-ids.json`)).json();
 await page.goto(`${BASE_URL}/clienti/${ids.legacy}/modifica`);await page.waitForLoadState('networkidle');
 await expect(page.locator('[name="type_cliente"]')).toHaveValue('');
 await expect(page.locator('#customer-ciudadania-reg')).toHaveValue('');
 await expect(page.locator('[name="country_reg"]')).toHaveValue('');
 await page.goto(`${BASE_URL}/clienti/${ids.ospite}/modifica`);await page.waitForLoadState('networkidle');
 for(const id of ['steparrow-gen-info','steparrow-description-info','steparrow-azienda-info','steparrow-contact-info']) {
  await page.locator(`#${id}-tab`).click();await expect(page.locator(`#${id}`)).toBeVisible();
  const size=await page.evaluate(()=>({viewport:innerWidth,width:document.documentElement.scrollWidth}));
  console.log('Dimensioni scheda sintetica',id,size);
  expect(size.width,`Overflow nella scheda ${id}`).toBeLessThanOrEqual(size.viewport);
 }
 await page.locator('#steparrow-gen-info-tab').press('Enter');
 await expect(page.locator('#steparrow-gen-info')).toBeVisible();
 await page.locator('#steparrow-gen-info-tab').press('ArrowRight');
 await expect(page.locator('#steparrow-description-info')).toBeVisible();
 await page.getByRole('button',{name:/Salva a cliente/}).click();
 await expect(page.getByRole('button',{name:'Sì, modifica',exact:true})).toBeVisible();
 await page.keyboard.press('Escape');
 await expect(page.getByRole('button',{name:'Sì, modifica',exact:true})).not.toBeVisible();
 expect(customerWrites).toBe(0);
});
