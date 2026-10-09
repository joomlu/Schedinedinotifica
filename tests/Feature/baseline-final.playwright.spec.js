import {test,expect,BASE_URL} from '../Support/playwright.js';
test.use({headless:true});
test('Gruppo anonimo: accompagnatore esistente visibile nel modulo reale',async({page})=>{
 const ids=await (await page.request.get(`${BASE_URL}/baseline-final-ids.json`)).json();
 expect((await page.goto(`${BASE_URL}/w/${ids.short}`)).status()).toBe(200);
 await page.getByRole('link',{name:/Apri il tuo Web Check-in/}).click();
 await expect(page.locator('#schedina-form')).toBeVisible();
 await expect(page.locator('input[value="ACCOMPAGNATORE-BROWSER-SINTETICO"]')).toHaveCount(1);
});
test('Reception: reset password legacy vietato anche tramite sessione browser',async({page})=>{
 const ids=await (await page.request.get(`${BASE_URL}/baseline-final-ids.json`)).json();
 await page.goto(`${BASE_URL}/login`);
 await page.getByLabel(/Nome di accesso o email/i).fill('audit-reception');
 await page.getByLabel(/Password personale/i).fill('Password-audit-123!');
 await page.getByRole('button',{name:/Entra/i}).click();
 await expect(page).not.toHaveURL(/\/login$/);
 await page.goto(`${BASE_URL}/gestione-operativa`);
 const csrf=await page.locator('input[name="_token"]').first().inputValue();
 const response=await page.request.post(`${BASE_URL}/strutture/utenti/${ids.target}/reset`,{form:{_token:csrf,password:'Password-audit-nuova-123!'},maxRedirects:0});
 expect(response.status()).toBe(403);
});
