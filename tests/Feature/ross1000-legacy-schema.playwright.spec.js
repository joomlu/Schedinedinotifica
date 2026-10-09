import {test,expect,BASE_URL} from '../Support/playwright.js';
test.use({headless:true});
test('Ross1000: schema vecchio riproduce500, migration sintetica ripristina pagina200',async({page})=>{
 await page.goto(`${BASE_URL}/login`);
 await page.getByLabel(/Nome di accesso o email/i).fill('ross-completa');
 await page.getByLabel(/Password personale/i).fill('Password-ross-sintetica-123!');
 await page.getByRole('button',{name:/Entra/i}).click();await expect(page).not.toHaveURL(/\/login$/);
 await page.goto(`${BASE_URL}/struttura`);
 const failure=page.waitForResponse(r=>r.url()===`${BASE_URL}/istat-tabella-a`&&r.request().isNavigationRequest());
 await page.getByRole('link',{name:'Configurazione Ross1000',exact:true}).click();expect((await failure).status()).toBe(500);
 await page.goto(`${BASE_URL}/struttura`);const token=await page.locator('input[name="_token"]').first().inputValue();
 const upgraded=await page.request.post(`${BASE_URL}/audit-ross-schema/upgrade`,{form:{_token:token}});expect(upgraded.status()).toBe(200);expect(await upgraded.json()).toEqual({exit:0,snapshot:true});
 const response=page.waitForResponse(r=>r.url()===`${BASE_URL}/istat-tabella-a`&&r.request().isNavigationRequest());
 await page.getByRole('link',{name:'Configurazione Ross1000',exact:true}).click();expect((await response).status()).toBe(200);
 await expect(page.locator('#istat-area')).toBeVisible();await expect(page.getByRole('button',{name:'Invia direttamente',exact:true})).toBeDisabled();
});
