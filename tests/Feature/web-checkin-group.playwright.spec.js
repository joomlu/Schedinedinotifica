import {test,expect,BASE_URL} from '../Support/playwright.js';
test.use({headless:true});
for(const mode of ['full','short']) {
 test(`Accompagnatori: modifica, salvataggio e riapertura tramite ${mode}`,async({page})=>{
  const errors=[];
  page.on('pageerror',error=>{errors.push(error.message);console.log('Errore JavaScript:',error.message);});
  const ids=await (await page.request.get(`${BASE_URL}/baseline-final-ids.json`)).json();
  const url=mode==='full'?`${BASE_URL}/checkin/${ids.full}`:`${BASE_URL}/w/${ids.short}`;
  expect((await page.goto(url)).status()).toBe(200);
  if(mode==='short') {
   await page.getByRole('link',{name:/Apri il tuo Web Check-in/}).click();
   await page.waitForLoadState('load');
  }
  await expect(page.locator('#schedina-form')).toBeVisible();
  await page.locator('#schedina-step-comp').click();
  await expect(page.locator('#schedina-step-comp-pane')).toBeVisible();
  const name=page.locator('#componenti-container input[name$="[name]"]').first();
  if(!await name.isVisible()) await page.locator('#componenti-container .toggle-componente-details').first().click();
  await expect(name).toBeVisible();
  await name.fill(`ACCOMPAGNATORE-AGGIORNATO-${mode}`);
  await page.locator('button[name="save_mode"][value="web"]:visible').first().click();
  const confirm=page.getByRole('button',{name:/Sì, salva/});
  if(await confirm.isVisible()) await confirm.click();
  await expect(page.locator('#schedina-form')).toHaveCount(0);
  expect((await page.goto(`${BASE_URL}/checkin/${ids.full}`)).status()).toBe(200);
  await page.locator('#schedina-step-comp').click();
  await expect(page.locator('#componenti-container input[name$="[name]"]').first()).toHaveValue(`ACCOMPAGNATORE-AGGIORNATO-${mode}`);
  await expect(page.locator('#componenti-container input[name$="[id]"]')).toHaveCount(1);
  expect(errors).toEqual([]);
 });
}
