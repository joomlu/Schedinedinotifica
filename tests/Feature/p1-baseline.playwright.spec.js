import {test,expect,BASE_URL} from '../Support/playwright.js';
test.use({headless:true});
async function login(page,user) {
 await page.goto(`${BASE_URL}/login`);
 await page.getByLabel(/Nome di accesso o email/i).fill(user);
 await page.getByLabel(/Password personale/i).fill('Password-tassa-fixture-123!');
 await page.getByRole('button',{name:/Entra/i}).click();
 await expect(page).not.toHaveURL(/\/login$/);
}
async function closeConfirm(page) {
 const yes=page.getByRole('button',{name:'Sì, salva',exact:true});
 if(await yes.isVisible()) await yes.click();
 const ok=page.getByRole('button',{name:'OK',exact:true});
 if(await ok.isVisible()) await ok.click();
}
test('Impersonazione e uscita dalla topbar con identità originale',async({page})=>{
 await login(page,'p1-super');
 const ids=await (await page.request.get(`${BASE_URL}/p1-baseline-ids.json`)).json();
 await page.goto(`${BASE_URL}/superadmin/impersonazione`);
 await page.locator(`form[action="${BASE_URL}/superadmin/impersona/${ids.target}"] button`).click();
 await closeConfirm(page);
 await expect(page.locator('.user-name-sub-text')).toContainText('Struttura');
 expect((await page.request.get(`${BASE_URL}/superadmin/impersonazione`)).status()).toBe(403);
 await page.waitForLoadState('networkidle');
 await closeConfirm(page);
 await expect(page.locator('form[action$="/superadmin/impersona/esci"]')).toHaveCount(1);
 await page.locator('#page-header-user-dropdown').click();
 await expect(page.locator('.topbar-user .dropdown-menu')).toBeVisible();
 await page.getByRole('button',{name:/Esci impersonazione/}).click();
 await closeConfirm(page);
 await expect(page.locator('.user-name-sub-text')).toContainText('Super Admin');
 await page.waitForLoadState('networkidle');
 await closeConfirm(page);
 expect((await page.request.get(`${BASE_URL}/superadmin/impersonazione`)).status()).toBe(200);
});
test('Navigazione principale e Web Check-in coerente visibile',async({page})=>{
 await login(page,'tassa-anteprima');
 const ids=await (await page.request.get(`${BASE_URL}/p1-baseline-ids.json`)).json();
 for(const path of ['/dashboard','/clienti','/clienti/nuovo','/schedine','/schedine/nuova','/arrivi','/arrivi/nuovo','/web-checkin']) {
  const response=await page.goto(`${BASE_URL}${path}`);
  expect(response.status(),path).toBe(200);
  await expect(page.locator('#page-topbar')).toBeVisible();
 }
 await page.goto(`${BASE_URL}/schedine/${ids.positivo}/modifica`);
 await expect(page.locator('#schedina-form')).toBeVisible();
 await page.locator('#schedina-step-comp').click();
 await expect(page.locator('#schedina-step-comp-pane')).toBeVisible();
 await page.goto(`${BASE_URL}/logout`);
 await page.goto(`${BASE_URL}/checkin/${ids.token}`);
 await expect(page.locator('body')).toContainText('Anteprima positivo');
});
