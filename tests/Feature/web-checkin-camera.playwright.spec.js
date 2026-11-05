import {test,expect,BASE_URL} from '../Support/playwright.js';
test.use({headless:true});
for(const mode of ['full','short']) {
 test(`Camere: sostituzione e salvataggio ripetuto tramite ${mode}`,async({page})=>{
  const errors=[];
  page.on('pageerror',error=>errors.push(error.message));
  const ids=await (await page.request.get(`${BASE_URL}/baseline-final-ids.json`)).json();
  for(const numero of ['12','12','13']) {
   const url=mode==='full'?`${BASE_URL}/checkin/${ids.full}`:`${BASE_URL}/w/${ids.short}`;
   expect((await page.goto(url)).status()).toBe(200);
   if(mode==='short') {
    await page.getByRole('link',{name:/Apri il tuo Web Check-in/}).click();
    await page.waitForLoadState('load');
   }
   await expect(page.locator('#schedina-form')).toBeVisible();
   const camere=page.locator('#camere-container input[name$="[numero_camera]"]');
   await expect(camere).toHaveCount(1);
   await expect(camere.first()).toBeVisible();
   await camere.first().fill(numero);
   await page.locator('button[name="save_mode"][value="web"]:visible').first().click();
   const confirm=page.getByRole('button',{name:/Sì, salva/});
   if(await confirm.isVisible()) await confirm.click();
   await expect(page.locator('#schedina-form')).toHaveCount(0);
   expect((await page.goto(`${BASE_URL}/checkin/${ids.full}`)).status()).toBe(200);
   await expect(camere).toHaveCount(1);
   await expect(camere.first()).toHaveValue(numero);
  }
  expect(errors).toEqual([]);
 });
}
