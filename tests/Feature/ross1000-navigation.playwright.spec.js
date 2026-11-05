import {test,expect,BASE_URL} from '../Support/playwright.js';
test.use({headless:true});
for(const name of ['incompleta','completa','soggiorno-incompleto']){
 test(`Ross1000: bottone e Tabella A, struttura sintetica ${name}`,async({page})=>{
  const errors=[];page.on('pageerror',error=>errors.push(error.message));
  await page.goto(`${BASE_URL}/login`);
  await page.getByLabel(/Nome di accesso o email/i).fill(`ross-${name}`);
  await page.getByLabel(/Password personale/i).fill('Password-ross-sintetica-123!');
  await page.getByRole('button',{name:/Entra/i}).click();
  await expect(page).not.toHaveURL(/\/login$/);
  expect((await page.goto(`${BASE_URL}/struttura`)).status()).toBe(200);
  const link=page.getByRole('link',{name:'Configurazione Ross1000',exact:true});
  await expect(link).toBeVisible();
  await expect(link).toHaveAttribute('href',`${BASE_URL}/istat-tabella-a`);
  const response=page.waitForResponse(r=>r.url()===`${BASE_URL}/istat-tabella-a`&&r.request().isNavigationRequest());
  await link.click();expect((await response).status()).toBe(200);
  await expect(page.locator('#istat-area')).toBeVisible();
  await expect(page.getByLabel('Username WebService',{exact:true})).toHaveValue('');
  await expect(page.getByLabel('Password WebService',{exact:true})).toHaveValue('');
  await expect(page.getByRole('button',{name:'Invia direttamente',exact:true})).toBeDisabled();
  await expect(page.locator('body')).not.toContainText('QueryException');
  expect(errors).toEqual([]);
 });
}
