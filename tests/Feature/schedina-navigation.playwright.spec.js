import {test,expect,BASE_URL} from '../Support/playwright.js';
test.use({headless:true});
for(const width of [1440,768,390]) test(`Tre sezioni Schedine a ${width}px`,async({page})=>{
 await page.setViewportSize({width,height:900});
 await page.goto(`${BASE_URL}/login`);
 await page.locator('[name="login"]').fill('fixture-super_admin');
 await page.locator('[name="password"]').fill('Password-fixture-123!');
 await page.getByRole('button',{name:'Entra',exact:true}).click();
 await expect(page).not.toHaveURL(/\/login$/);
 await page.goto(`${BASE_URL}/clienti/nuovo`);
 const reference=await page.locator('.customer-entry-nav .nav-link.active').evaluate(e=>{
  const s=getComputedStyle(e);return [s.backgroundImage,s.color,s.fontSize,s.fontWeight,s.padding,s.borderRadius];
 });
 let writes=0;page.on('request',r=>{if(['POST','PUT','PATCH','DELETE'].includes(r.method())&&new URL(r.url()).pathname.startsWith('/schedine'))writes++;});
 await page.goto(`${BASE_URL}/schedine?q=Sintetico&page=2`);
 const nav=page.getByRole('navigation',{name:'Nuova / Elenco / Bozze schedine'});
 await expect(nav.locator('[aria-current="page"]')).toHaveText('Elenco schedine');
 await expect(page.locator('#sidebarTickets').getByRole('link',{name:'Nuova / Elenco / Bozze',exact:true})).toHaveCount(1);
 await expect(page.locator('#schedineTable')).toBeAttached();
 await nav.getByRole('link',{name:'Nuova schedina',exact:true}).focus();await page.keyboard.press('Enter');
 await expect(nav.locator('[aria-current="page"]')).toHaveText('Nuova schedina');
 expect(await nav.locator('.active').evaluate(e=>{
  const s=getComputedStyle(e);return [s.backgroundImage,s.color,s.fontSize,s.fontWeight,s.padding,s.borderRadius];
 })).toEqual(reference);
 await expect(page.locator('form.form-steps')).toBeAttached();
 for(const link of await nav.getByRole('link').all()){
  const box=await link.boundingBox();expect(box.height).toBeGreaterThanOrEqual(44);expect(box.x).toBeGreaterThanOrEqual(0);expect(box.x+box.width).toBeLessThanOrEqual(width);
 }
 await nav.getByRole('link',{name:'Nuova schedina',exact:true}).focus();await page.keyboard.press('Tab');
 await expect(nav.getByRole('link',{name:'Elenco schedine',exact:true})).toBeFocused();await page.keyboard.press('Enter');
 await expect(page).toHaveURL(/q=Sintetico&page=2/);
 await nav.getByRole('link',{name:'Bozze',exact:true}).click();
 await expect(nav.locator('[aria-current="page"]')).toHaveText('Bozze');
 await expect(page.getByRole('link',{name:'Riprendi compilazione',exact:true})).toHaveCount(1);
 await page.getByRole('link',{name:'Riprendi compilazione',exact:true}).click();
 await expect(nav.locator('[aria-current="page"]')).toHaveText('Bozze');
 await expect(page.locator('form.form-steps')).toBeAttached();
 await expect(page.locator('[name="name"]').first()).toHaveValue('BozzaNavigazione');
 expect(writes).toBe(0);
 console.log(JSON.stringify({viewport:width,stile_uguale_a_Clienti:true,tastiera:true,contesto_conservato:true,ripresa_bozza:true,scritture_navigazione:writes}));
});
