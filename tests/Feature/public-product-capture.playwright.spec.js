import { test, expect, BASE_URL } from '../Support/playwright.js';
import { createHash } from 'node:crypto';
import { writeFileSync } from 'node:fs';
test('Catture autentiche del prodotto con soli dati dimostrativi', async ({page}, info) => {
 await page.setViewportSize({width:1440,height:1000});
 const ids=await (await page.request.get(`${BASE_URL}/public-product-demo-ids.json`)).json();
 await page.goto(`${BASE_URL}/login`);
 await page.locator('[name=login]').fill('tassa-anteprima');
 await page.locator('[name=password]').fill('Password-tassa-fixture-123!');
 await page.getByRole('button',{name:'Entra',exact:true}).click();
 await expect(page).not.toHaveURL(/\/login$/);
 const captures=[];
 for (const [name,path,selector] of [
  ['arrivi','/arrivi','.page-content'],
  ['richiesta',`/web-checkin/${ids.web}/modifica`,'.page-content'],
  ['schedina',`/schedine/${ids.schedina}/modifica`,'.page-content'],
  ['tassa',`/schedine/${ids.schedina}/tassa/anteprima`,'#ricevuta-tassa-card'],
 ]) {
  expect((await page.goto(BASE_URL+path,{waitUntil:'load'})).status()).toBe(200);
  await expect(page.locator(selector)).toBeVisible();
  await page.evaluate(()=>document.fonts.ready);
  await page.evaluate(()=>window.scrollTo({top:0,behavior:'instant'}));
  const box=await page.locator(selector).boundingBox();
  const image=await page.screenshot({path:info.outputPath(`prodotto-${name}.png`),clip:{x:box.x,y:box.y,width:box.width,height:Math.min(box.height,900)}});
  captures.push({nome:name,origine:path.replace(/\/\d+/g,'/:id'),sha256:createHash('sha256').update(image).digest('hex')});
 }
 await page.goto(`${BASE_URL}/logout`);
 // L’alias canonico pubblico è /w/; l’accesso porta alla compilazione reale.
 await page.goto(`${BASE_URL}/w/${ids.short}`,{waitUntil:'load'});
 const open=page.getByRole('link',{name:/Apri il tuo Web Check-in/});
 if(await open.isVisible()) await open.click();
 await expect(page.locator('#schedina-form')).toBeVisible();
 const image=await page.screenshot({path:info.outputPath('prodotto-ospite.png'),fullPage:false});
 captures.push({nome:'ospite',origine:'/w/:token',sha256:createHash('sha256').update(image).digest('hex')});
 writeFileSync(info.outputPath('provenienza-catture.json'),JSON.stringify({dati:'esclusivamente sintetici',catture:captures},null,2));
});
