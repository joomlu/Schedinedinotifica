import {test,expect,BASE_URL} from '../Support/playwright.js';
for(const width of [1440,768,390]) test(`Selettori Questura coerenti e utilizzabili a ${width}px`, async({page})=>{
 test.setTimeout(90000); await page.setViewportSize({width,height:1000}); const errors=[];page.on('pageerror',e=>errors.push(e.message));
 await page.goto(BASE_URL+'/login');await page.locator('[name=login]').fill('questura-ui');await page.locator('[name=password]').fill('Password-Fixture-Questura-123!');await page.getByRole('button',{name:'Accedi',exact:true}).click();await page.getByRole('button',{name:'Sì, salva',exact:true}).click();const login=page.waitForResponse(r=>r.request().method()==='POST'&&new URL(r.url()).pathname==='/login');await page.getByRole('button',{name:'OK',exact:true}).click();await login;await expect(page).not.toHaveURL(/\/login$/);
 const fixture=await(await page.request.get(BASE_URL+'/questura-ui-fixture.json')).json();await page.goto(BASE_URL+'/struttura?sid='+fixture.struttura);
 const writes=[];page.on('request',r=>{if(!['GET','HEAD','OPTIONS'].includes(r.method()))writes.push(r.url());});
 for(const [id,options] of [['questura_profilo',[['standard','Standard'],['appartamenti','Gestione Appartamenti']]],['questura_modalita',[['txt','Solo TXT'],['manuale','Web Service manuale'],['automatico','Web Service automatico alle 23:00']]]]){
  const combo=page.locator('#'+id+' + .select2 [role=combobox]');await expect(combo).toBeVisible();
  for(const [value,label] of options){await combo.click();const option=page.getByRole('option',{name:label,exact:true});await expect(option).toBeVisible();const colors=await option.evaluate(el=>{const p=el.closest('.select2-dropdown');return {background:getComputedStyle(p).backgroundColor,color:getComputedStyle(el).color};});expect(colors.background).not.toBe('rgb(0, 0, 0)');await option.click();await expect(page.locator('#'+id)).toHaveValue(value);}
  await combo.click();await page.keyboard.press('Escape');await page.keyboard.press('Enter');await expect(page.locator('.select2-container--open .select2-results')).toBeVisible();await page.keyboard.press('Escape');
 }
 expect(await page.evaluate(()=>document.documentElement.scrollWidth<=innerWidth)).toBeTruthy();expect(errors).toEqual([]);expect(writes).toEqual([]);
 console.log(JSON.stringify({larghezza:width,profili:2,modalita:3,tastiera:true,scritture:0,errori_js:0}));
});
