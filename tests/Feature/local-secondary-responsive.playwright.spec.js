import {test,expect,BASE_URL} from '../Support/playwright.js';
test.use({headless:true});
test.setTimeout(90000);
async function login(page,login,password){
 await page.goto(`${BASE_URL}/login`);
 await page.getByLabel(/Nome di accesso o email/i).fill(login);
 await page.getByLabel(/Password personale/i).fill(password);
 await page.getByRole('button',{name:/Entra/i}).click();
 await expect(page).not.toHaveURL(/\/login$/);
}
for(const viewport of [{width:1440,height:900},{width:390,height:844}]){
 test(`Navigazione moduli secondari e viewport ${viewport.width}`,async({page})=>{
  await page.setViewportSize(viewport);
  const errors=[];page.on('pageerror',error=>errors.push(error.message));
  await login(page,'accettazione-locale','Password-audit-123!');
  for(const path of ['/supporto','/calendario','/notifiche','/clienti/import','/cestino','/gestione-operativa']){
   expect((await page.goto(`${BASE_URL}${path}`)).status(),path).toBe(200);
   await expect(page.locator('#page-topbar')).toBeVisible();
   await expect(page.locator('.main-content')).toBeVisible();
   await expect(page.locator('body')).not.toContainText('QueryException');
   expect(await page.locator('.main-content').evaluate(el=>el.getBoundingClientRect().width)).toBeLessThanOrEqual(viewport.width);
  }
  await page.goto(`${BASE_URL}/logout`);
  await login(page,'p1-super','Password-tassa-fixture-123!');
  for(const path of ['/superadmin/crm','/superadmin/pagamenti','/geo/comuni/logo','/superadmin/strutture','/superadmin/proprietari','/superadmin/amministratori']){
   expect((await page.goto(`${BASE_URL}${path}`)).status(),path).toBe(200);
   await expect(page.locator('.main-content')).toBeVisible();
   await expect(page.locator('body')).not.toContainText('ErrorException');
   expect(await page.locator('.main-content').evaluate(el=>el.getBoundingClientRect().width)).toBeLessThanOrEqual(viewport.width);
  }
  expect(errors).toEqual([]);
 });
}
