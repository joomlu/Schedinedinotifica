import {test,expect,BASE_URL} from '../Support/playwright.js';
test.use({headless:true});
for(const attempt of [1,2,3]) for(const mode of ['full','short']) {
 test(`Camere: sostituzione e salvataggio ripetuto tramite ${mode}, campione ${attempt}`,async({page})=>{
  const errors=[];
  const started=Date.now();
  const trace=[];
  const record=(event,request,extra={})=>trace.push({ms:Date.now()-started,event,path:new URL(request.url()).pathname,type:request.resourceType(),...extra});
  page.on('request',request=>record('richiesta',request));
  page.on('response',response=>record('risposta',response.request(),{status:response.status()}));
  page.on('requestfailed',request=>record('fallimento',request,{error:request.failure()?.errorText}));
  try {
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
  } finally { console.log('TRACCIA_CAMERE '+JSON.stringify({attempt,mode,trace,errors})); }
 });
}
